<?php

namespace Tests\Unit\Mail;

use App\Enums\UserRole;
use App\Mail\AdminReportMail;
use App\Models\User;
use Tests\TestCase;

class AdminReportMailTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function report(): array
    {
        return [
            'type' => 'mantenimientos',
            'title' => 'Reporte de mantenimientos',
            'summary' => ['Mantenimientos' => 3, 'Costo total' => '$120.00'],
            'filters_label' => 'Todos los vehículos',
            'columns' => ['Orden', 'Vehículo'],
            'rows' => [['OS-2026-0001', 'ABC-123 — Toyota Corolla']],
        ];
    }

    private function mailable(): AdminReportMail
    {
        $admin = (new User)->forceFill([
            'id' => 1,
            'name' => 'Admin Demo',
            'email' => 'admin@autogest.test',
            'role' => UserRole::Admin,
        ]);

        return new AdminReportMail($this->report(), $admin);
    }

    public function test_the_subject_is_prefixed_with_the_app_name(): void
    {
        $this->assertSame('AutoGest — Reporte de mantenimientos', $this->mailable()->envelope()->subject);
    }

    public function test_the_content_passes_the_report_data_to_the_view(): void
    {
        $content = $this->mailable()->content();

        $this->assertSame('mail.admin-report', $content->view);
        $this->assertSame('Reporte de mantenimientos', $content->with['title']);
        $this->assertSame('Admin Demo', $content->with['adminName']);
        $this->assertSame(['Mantenimientos' => 3, 'Costo total' => '$120.00'], $content->with['summary']);
        $this->assertSame('Todos los vehículos', $content->with['filtersLabel']);
        $this->assertSame(now()->format('d/m/Y H:i'), $content->with['generatedAt']);
    }

    public function test_it_attaches_the_report_as_a_dated_pdf(): void
    {
        $attachments = $this->mailable()->attachments();

        $this->assertCount(1, $attachments);
        $this->assertSame('reporte-mantenimientos-'.now()->format('Y-m-d').'.pdf', $attachments[0]->as);
        $this->assertSame('application/pdf', $attachments[0]->mime);
    }

    public function test_the_rendered_email_shows_the_report_summary(): void
    {
        $rendered = $this->mailable()->render();

        $this->assertStringContainsString('Reporte de mantenimientos', $rendered);
        $this->assertStringContainsString('Mantenimientos', $rendered);
        $this->assertStringContainsString('$120.00', $rendered);
    }
}
