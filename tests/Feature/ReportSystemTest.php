<?php

namespace Tests\Feature;

use App\Data\ReportData;
use App\Livewire\Reports\Create;
use App\Livewire\Templates\Editor;
use App\Models\Report;
use App\Models\Template;
use App\Models\User;
use App\Services\ReportService;
use App\Support\HtmlSanitizer;
use Database\Seeders\PozoATierraTemplateSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ReportSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class, PozoATierraTemplateSeeder::class]);
    }

    private function admin(): User
    {
        return User::where('email', 'admin@informes.test')->first();
    }

    private function tech(): User
    {
        return User::where('email', 'tecnico@informes.test')->first();
    }

    public function test_guests_only_see_the_login(): void
    {
        $this->get('/')->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/login')->assertOk();
        $this->get('/register')->assertNotFound();
        $this->get('/forgot-password')->assertNotFound();
    }

    public function test_inactive_users_cannot_log_in(): void
    {
        $this->tech()->update(['is_active' => false]);

        $this->post('/login', ['email' => 'tecnico@informes.test', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_technicians_cannot_access_admin_modules(): void
    {
        $this->actingAs($this->tech());

        $this->get('/dashboard')->assertOk();
        $this->get('/informes/nuevo')->assertOk();
        $this->get('/plantillas')->assertForbidden();
        $this->get('/usuarios')->assertForbidden();
        $this->get('/ajustes')->assertForbidden();
    }

    public function test_admin_pages_render(): void
    {
        $this->actingAs($this->admin());
        $template = Template::first();

        foreach (['/dashboard', '/informes', '/informes/nuevo', '/plantillas', '/plantillas/nueva', "/plantillas/{$template->id}/editar", '/usuarios', '/ajustes', '/user/profile'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_a_report_is_created_from_the_template_with_its_pdf(): void
    {
        $this->actingAs($this->tech());
        $template = Template::first();

        $component = Livewire::test(Create::class)->call('useTemplate', $template->id);
        $sections = $component->get('sections');
        $gallery = collect($sections)->firstWhere('type', 'gallery');

        // Sin imágenes no se puede guardar.
        $component->set('form.title', 'Informe de prueba')->call('save')->assertHasErrors();

        foreach (array_keys($gallery['content']['items']) as $i) {
            $component->set("galleryItemUploads.{$gallery['uid']}.{$i}", UploadedFile::fake()->image("foto{$i}.jpg", 800, 600));
        }

        // Firma del cliente (nombre, cargo e imagen).
        $component->set('form.client_signature.name', 'Juan Ramos')
            ->set('form.client_signature.position', 'Supervisor')
            ->set('clientSignatureUpload', UploadedFile::fake()->image('firma.png', 300, 120))
            ->assertHasNoErrors();

        // Sección personalizada agregada desde el modal.
        $component->call('openAddModal')
            ->call('selectType', 'richtext')
            ->set('newTitle', 'Observaciones')
            ->call('addSection')
            ->assertHasNoErrors();

        $component->call('save')->assertHasNoErrors()->assertRedirect();

        $report = Report::with('sections')->firstOrFail();
        $this->assertSame('INF-'.now()->year.'-0001', $report->code);
        $this->assertCount(7, $report->sections);
        $this->assertSame('Observaciones', $report->sections->last()->title);
        $this->assertSame('Juan Ramos', $report->client_signature['name']);
        $this->assertSame('Supervisor', $report->client_signature['position']);
        Storage::disk('public')->assertExists($report->client_signature['path']);
        Storage::disk('local')->assertExists($report->pdf_path);

        $this->get(route('reports.pdf', $report))->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    private function adminReport(): Report
    {
        return app(ReportService::class)->create(ReportData::fromArray([
            'title' => 'Privado', 'service_date' => '2026-09-01',
            'sections' => [['title' => 'Texto', 'type' => 'richtext', 'content' => ['html' => '<p>Hola</p>']]],
        ]), $this->admin());
    }

    public function test_technicians_cannot_see_reports_of_others(): void
    {
        $report = $this->adminReport();

        $this->actingAs($this->tech())->get(route('reports.show', $report))->assertForbidden();
    }

    public function test_technicians_cannot_download_reports_of_others(): void
    {
        $report = $this->adminReport();

        $this->actingAs($this->tech())->get(route('reports.download', $report))->assertForbidden();
    }

    public function test_admins_can_see_every_report(): void
    {
        $report = $this->adminReport();

        $this->actingAs($this->admin())->get(route('reports.show', $report))->assertOk();
    }

    public function test_reports_can_be_duplicated(): void
    {
        $source = app(ReportService::class)->create(ReportData::fromArray([
            'title' => 'Original', 'service_date' => '2026-09-01',
            'sections' => [['title' => 'Texto', 'type' => 'richtext', 'content' => ['html' => '<p>Hola</p>']]],
        ]), $this->tech());

        $this->actingAs($this->tech());
        Livewire::test(Create::class, ['duplicateId' => $source->id])
            ->assertSet('editing', true)
            ->assertSet('form.title', 'Original')
            ->call('save')
            ->assertHasNoErrors();

        $copy = Report::latest('id')->first();
        $this->assertNotSame($source->code, $copy->code);
        $this->assertSame($source->id, $copy->duplicated_from_id);
    }

    public function test_a_report_can_be_saved_as_draft_and_finished_later(): void
    {
        $this->actingAs($this->tech());
        $template = Template::first();

        // "Seguir editando después": se guarda sin imágenes, sin PDF y sin código.
        Livewire::test(Create::class)
            ->call('useTemplate', $template->id)
            ->set('form.title', 'Informe a medias')
            ->call('saveDraft')
            ->assertHasNoErrors()
            ->assertRedirect(route('reports.index'));

        $draft = Report::firstOrFail();
        $this->assertTrue($draft->isDraft());
        $this->assertNull($draft->code);
        $this->assertNull($draft->pdf_path);
        $this->assertCount(6, $draft->sections);

        // Se retoma el borrador, se completa y se finaliza.
        $component = Livewire::test(Create::class, ['draftId' => $draft->id])
            ->assertSet('editing', true)
            ->assertSet('form.title', 'Informe a medias');

        $gallery = collect($component->get('sections'))->firstWhere('type', 'gallery');
        foreach (array_keys($gallery['content']['items']) as $i) {
            $component->set("galleryItemUploads.{$gallery['uid']}.{$i}", UploadedFile::fake()->image("foto{$i}.jpg", 800, 600));
        }

        $component->set('form.title', 'Informe terminado')->call('save')->assertHasNoErrors();

        $this->assertSame(1, Report::count());
        $report = $draft->fresh();
        $this->assertTrue($report->isFinal());
        $this->assertSame('Informe terminado', $report->title);
        $this->assertSame('INF-'.now()->year.'-0001', $report->code);
        $this->assertNotNull($report->finalized_at);
        Storage::disk('local')->assertExists($report->pdf_path);
    }

    private function draftOf(User $user): Report
    {
        return app(ReportService::class)->saveDraft(ReportData::fromArray([
            'title' => 'Pendiente', 'service_date' => '2026-09-01',
            'sections' => [['title' => 'Texto', 'type' => 'richtext', 'content' => ['html' => '<p>Hola</p>']]],
        ]), $user);
    }

    public function test_a_draft_has_no_pdf_to_view(): void
    {
        $draft = $this->draftOf($this->tech());

        $this->actingAs($this->tech())->get(route('reports.pdf', $draft))->assertForbidden();
    }

    public function test_a_draft_cannot_be_downloaded(): void
    {
        $draft = $this->draftOf($this->tech());

        $this->actingAs($this->tech())->get(route('reports.download', $draft))->assertForbidden();
    }

    public function test_only_the_author_can_keep_editing_a_draft(): void
    {
        $draft = $this->draftOf($this->tech());

        $this->assertTrue($this->tech()->can('update', $draft));
        $this->assertFalse($this->admin()->can('update', $draft));
        $this->assertFalse($this->tech()->can('duplicate', $draft));

        $this->actingAs($this->admin())->get(route('reports.create', ['borrador' => $draft->id]))->assertForbidden();
    }

    public function test_a_finalized_report_can_no_longer_be_edited(): void
    {
        $report = $this->adminReport();

        $this->assertFalse($this->admin()->can('update', $report));
        $this->actingAs($this->admin())->get(route('reports.create', ['borrador' => $report->id]))->assertForbidden();

        $this->expectException(\LogicException::class);
        app(ReportService::class)->saveDraft(ReportData::fromArray([
            'title' => 'Cambio', 'service_date' => '2026-09-01', 'sections' => [],
        ]), $this->admin(), $report);
    }

    public function test_draft_page_renders_without_pdf_actions(): void
    {
        $draft = $this->draftOf($this->tech());

        $this->actingAs($this->tech())->get(route('reports.show', $draft))
            ->assertOk()
            ->assertSee('Seguir editando')
            ->assertDontSee('Descargar PDF');
    }

    public function test_templates_can_be_created_with_custom_sections(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(Editor::class)
            ->set('form.name', 'Tableros')
            ->call('openAddModal')->call('selectType', 'table')->set('newTitle', 'Mediciones')->call('addSection')
            ->call('openAddModal')->call('selectType', 'key_value')->set('newTitle', 'Datos')->call('addSection')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('templates.index'));

        $this->assertSame(2, Template::where('name', 'Tableros')->first()->sections()->count());
    }

    public function test_rich_text_is_sanitized(): void
    {
        $html = HtmlSanitizer::clean('<p onclick="x()">Hola <script>alert(1)</script><strong style="color: red; position: fixed">x</strong><img src="https://evil.test/a.png"><img src="/storage/uploads/a.jpg"></p>');

        $this->assertStringNotContainsString('script', $html);
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('evil.test', $html);
        $this->assertStringNotContainsString('position', $html);
        $this->assertStringContainsString('/storage/uploads/a.jpg', $html);
    }
}
