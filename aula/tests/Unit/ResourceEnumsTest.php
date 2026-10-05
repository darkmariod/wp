<?php

namespace Tests\Unit;

use App\Enums\ResourceStatus;
use App\Enums\ResourceType;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Enums de la Biblioteca: etiquetas en español y reglas por tipo de recurso.
 */
class ResourceEnumsTest extends TestCase
{
    /**
     * @return array<string, array{0: ResourceType, 1: string, 2: string, 3: bool, 4: bool, 5: bool, 6: bool, 7: bool}>
     */
    public static function tiposDeRecurso(): array
    {
        // tipo, etiqueta, icono, usaArchivo, usaUrl, exigeArchivo, exigeUrl, seVeEnLinea
        return [
            'pdf' => [ResourceType::Pdf, 'PDF', 'pdf', true, false, true, false, true],
            'documento' => [ResourceType::Document, 'Documento', 'document', true, false, true, false, false],
            'presentación' => [ResourceType::Presentation, 'Presentación', 'presentation', true, false, true, false, false],
            'video' => [ResourceType::Video, 'Video', 'video', true, true, false, false, true],
            'audio' => [ResourceType::Audio, 'Audio', 'audio', true, false, true, false, true],
            'imagen' => [ResourceType::Image, 'Imagen', 'image', true, false, true, false, true],
            'enlace' => [ResourceType::Link, 'Enlace', 'link', false, true, false, true, false],
            'archivo' => [ResourceType::File, 'Archivo', 'file', true, false, true, false, false],
        ];
    }

    #[DataProvider('tiposDeRecurso')]
    public function test_cada_tipo_declara_su_etiqueta_icono_y_reglas(
        ResourceType $tipo,
        string $etiqueta,
        string $icono,
        bool $usaArchivo,
        bool $usaUrl,
        bool $exigeArchivo,
        bool $exigeUrl,
        bool $seVeEnLinea,
    ): void {
        $this->assertSame($etiqueta, $tipo->label());
        $this->assertSame($icono, $tipo->icon());
        $this->assertSame($usaArchivo, $tipo->usesFile());
        $this->assertSame($usaUrl, $tipo->usesExternalUrl());
        $this->assertSame($exigeArchivo, $tipo->requiresFile());
        $this->assertSame($exigeUrl, $tipo->requiresExternalUrl());
        $this->assertSame($seVeEnLinea, $tipo->isViewableInline());
    }

    public function test_los_tipos_tienen_los_valores_de_texto_esperados(): void
    {
        $this->assertSame(
            ['pdf', 'document', 'presentation', 'video', 'audio', 'image', 'link', 'file'],
            array_map(fn (ResourceType $tipo) => $tipo->value, ResourceType::cases()),
        );
    }

    public function test_un_valor_desconocido_no_es_un_tipo_valido(): void
    {
        $this->assertNull(ResourceType::tryFrom('ejecutable'));
        $this->assertSame(ResourceType::Pdf, ResourceType::from('pdf'));
    }

    public function test_solo_el_enlace_exige_url_y_solo_el_enlace_no_usa_archivo(): void
    {
        $exigenUrl = array_filter(ResourceType::cases(), fn (ResourceType $tipo) => $tipo->requiresExternalUrl());
        $sinArchivo = array_filter(ResourceType::cases(), fn (ResourceType $tipo) => ! $tipo->usesFile());

        $this->assertSame([ResourceType::Link], array_values($exigenUrl));
        $this->assertSame([ResourceType::Link], array_values($sinArchivo));
    }

    public function test_el_estado_declara_etiqueta_y_color(): void
    {
        $this->assertSame(['draft', 'published', 'archived'], array_map(
            fn (ResourceStatus $estado) => $estado->value,
            ResourceStatus::cases(),
        ));

        $this->assertSame('Borrador', ResourceStatus::Draft->label());
        $this->assertSame('Publicado', ResourceStatus::Published->label());
        $this->assertSame('Archivado', ResourceStatus::Archived->label());

        $this->assertSame('gray', ResourceStatus::Draft->color());
        $this->assertSame('success', ResourceStatus::Published->color());
        $this->assertSame('warning', ResourceStatus::Archived->color());
    }
}
