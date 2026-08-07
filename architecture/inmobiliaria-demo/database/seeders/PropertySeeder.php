<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        $properties = [
            // Venta — destacadas
            [
                'title' => 'Casa familiar en sector Norte de Riobamba',
                'description' => '<p>Hermosa casa ubicada en el sector Norte de Riobamba, cerca de centros comerciales, colegios y parques. Cuenta con amplios espacios, cocina moderna, sala-comedor independiente, patio interior y garaje para dos vehículos. Ideal para familia que busca comodidad y seguridad.</p>',
                'price' => 185000, 'address' => 'Av. Leopoldo Freire y Juan Félix Proaño, Norte',
                'bedrooms' => 4, 'bathrooms' => 3, 'area_m2' => 180, 'parking_spaces' => 2,
                'sector_id' => 2, 'property_type_id' => 1, 'operation_id' => 1,
                'status' => 'available', 'is_featured' => true, 'published_at' => now()->subDays(2),
                'latitude' => -1.6650, 'longitude' => -78.6410,
            ],
            [
                'title' => 'Departamento moderno en el centro de Riobamba',
                'description' => '<p>Departamento completamente remodelado en pleno centro de Riobamba. Acabados de lujo, piso flotante, cocina integral con granito, baño con hidromasaje. Vista panorámica a la ciudad. A una cuadra del parque Maldonado.</p>',
                'price' => 95000, 'address' => 'Calle Primera Constituyente y Veloz, Centro',
                'bedrooms' => 3, 'bathrooms' => 2, 'area_m2' => 90, 'parking_spaces' => 1,
                'sector_id' => 1, 'property_type_id' => 2, 'operation_id' => 1,
                'status' => 'available', 'is_featured' => true, 'published_at' => now()->subDays(5),
                'latitude' => -1.6730, 'longitude' => -78.6480,
            ],
            [
                'title' => 'Terreno urbanizado en La Primavera',
                'description' => '<p>Terreno plano en el exclusivo sector de La Primavera. Cuenta con todos los servicios básicos, vías asfaltadas y cerramiento perimetral. Zona residencial de alto crecimiento. Perfecto para construir casa de campo o conjunto habitacional.</p>',
                'price' => 65000, 'address' => 'Vía a La Primavera km 3.5',
                'bedrooms' => 0, 'bathrooms' => 0, 'area_m2' => 320, 'parking_spaces' => 0,
                'sector_id' => 7, 'property_type_id' => 3, 'operation_id' => 1,
                'status' => 'available', 'is_featured' => true, 'published_at' => now()->subDays(1),
                'latitude' => -1.6500, 'longitude' => -78.6600,
            ],
            // Alquiler
            [
                'title' => 'Casa en alquiler sector San Alfonso',
                'description' => '<p>Acogedora casa en alquiler en San Alfonso, sector tranquilo y residencial. Cuenta con 3 dormitorios, sala-comedor, cocina, baño completo, patio y garaje. Agua potable y luz incluidos. Disponible desde inmediato.</p>',
                'price' => 450, 'address' => 'San Alfonso, calle principal s/n',
                'bedrooms' => 3, 'bathrooms' => 1, 'area_m2' => 120, 'parking_spaces' => 1,
                'sector_id' => 8, 'property_type_id' => 1, 'operation_id' => 2,
                'status' => 'available', 'is_featured' => false, 'published_at' => now()->subDays(3),
                'latitude' => -1.6800, 'longitude' => -78.6550,
            ],
            [
                'title' => 'Departamento amoblado en Lizarzaburu',
                'description' => '<p>Departamento amoblado en alquiler, sector Lizarzaburu. Cerca de la ESPOCH, centros comerciales y vías principales. Incluye cocina equipada, lavadora, refrigerador, camas, closets. Ideal para estudiantes o profesionales.</p>',
                'price' => 350, 'address' => 'Av. Canónigo Ramos, Lizarzaburu',
                'bedrooms' => 2, 'bathrooms' => 1, 'area_m2' => 65, 'parking_spaces' => 0,
                'sector_id' => 4, 'property_type_id' => 2, 'operation_id' => 2,
                'status' => 'available', 'is_featured' => false, 'published_at' => now()->subDays(7),
                'latitude' => -1.6680, 'longitude' => -78.6500,
            ],
            // Más propiedades de venta
            [
                'title' => 'Casa en condominio privado sur de Riobamba',
                'description' => '<p>Casa en condominio con vigilancia 24h, áreas verdes, cancha deportiva y salón comunal. Amplia sala-comedor, cocina con desayunador, 3 dormitorios con closet, 2 baños, jardín y garaje.</p>',
                'price' => 165000, 'address' => 'Urbanización Los Andes, sector Sur',
                'bedrooms' => 3, 'bathrooms' => 2, 'area_m2' => 150, 'parking_spaces' => 2,
                'sector_id' => 3, 'property_type_id' => 1, 'operation_id' => 1,
                'status' => 'available', 'is_featured' => false, 'published_at' => now()->subDays(10),
                'latitude' => -1.6900, 'longitude' => -78.6450,
            ],
            [
                'title' => 'Local comercial en centro de Riobamba',
                'description' => '<p>Local en la mejor zona comercial del centro. Esquina con gran vitrina, 50m² de área útil, baño privado, bodega interna. Ideal para restaurante, tienda o consultorio. Alto flujo peatonal y vehicular.</p>',
                'price' => 120000, 'address' => 'Av. Daniel León Borja y España, Centro',
                'bedrooms' => 0, 'bathrooms' => 1, 'area_m2' => 50, 'parking_spaces' => 0,
                'sector_id' => 1, 'property_type_id' => 4, 'operation_id' => 1,
                'status' => 'available', 'is_featured' => false, 'published_at' => now()->subDays(15),
                'latitude' => -1.6720, 'longitude' => -78.6470,
            ],
            [
                'title' => 'Terreno con vista en Yaruquíes',
                'description' => '<p>Terreno con pendiente suave y vista espectacular a la ciudad de Riobamba. Ideal para casa de campo o proyecto turístico. Servicios básicos en la vía principal. Acceso por carretera asfaltada.</p>',
                'price' => 45000, 'address' => 'Vía Yaruquíes, sector La Moya',
                'bedrooms' => 0, 'bathrooms' => 0, 'area_m2' => 500, 'parking_spaces' => 0,
                'sector_id' => 6, 'property_type_id' => 3, 'operation_id' => 1,
                'status' => 'available', 'is_featured' => false, 'published_at' => now()->subDays(20),
                'latitude' => -1.7100, 'longitude' => -78.6400,
            ],
            [
                'title' => 'Casa de campo en Maldonado',
                'description' => '<p>Hermosa casa de campo con terreno amplio en Maldonado. Sala comedor amplia, cocina con fogón de leña, 3 dormitorios, baño completo, porche, huerto y árboles frutales. Agua de vertiente propia. A 15 min del centro.</p>',
                'price' => 135000, 'address' => 'Vía Maldonado, km 7',
                'bedrooms' => 3, 'bathrooms' => 1, 'area_m2' => 200, 'parking_spaces' => 3,
                'sector_id' => 5, 'property_type_id' => 1, 'operation_id' => 1,
                'status' => 'available', 'is_featured' => false, 'published_at' => now()->subDays(12),
                'latitude' => -1.6550, 'longitude' => -78.6300,
            ],
            [
                'title' => 'Departamento estudio amoblado centro',
                'description' => '<p>Departamento tipo estudio, completamente amoblado. Ideal para una persona. Cocina americana, baño completo, balcón. Edificio con seguridad, ascensor y lavandería compartida. Cerca de todo.</p>',
                'price' => 280, 'address' => 'Calle 5 de Junio y Espejo, Centro',
                'bedrooms' => 1, 'bathrooms' => 1, 'area_m2' => 35, 'parking_spaces' => 0,
                'sector_id' => 1, 'property_type_id' => 2, 'operation_id' => 2,
                'status' => 'available', 'is_featured' => false, 'published_at' => now()->subDays(4),
                'latitude' => -1.6735, 'longitude' => -78.6485,
            ],
            [
                'title' => 'Casa quinta con piscina en La Primavera',
                'description' => '<p>Espectacular casa quinta con piscina privada, amplias áreas verdes, barbacoa, parqueadero para 4 autos. Sala, comedor, cocina abierta, 4 dormitorios con baño privado. Perfecta para fines de semana o eventos.</p>',
                'price' => 280000, 'address' => 'La Primavera, km 5 vía a Guano',
                'bedrooms' => 4, 'bathrooms' => 4, 'area_m2' => 350, 'parking_spaces' => 4,
                'sector_id' => 7, 'property_type_id' => 1, 'operation_id' => 1,
                'status' => 'available', 'is_featured' => true, 'published_at' => now()->subDays(0),
                'latitude' => -1.6450, 'longitude' => -78.6650,
            ],
            [
                'title' => 'Terreno comercial en sector Norte',
                'description' => '<p>Terreno comercial en la zona de mayor crecimiento del norte de Riobamba. Sobre avenida principal, frente amplio. Ideal para construir local comercial, bodega o conjunto de oficinas.</p>',
                'price' => 89000, 'address' => 'Av. Leopoldo Freire y Universitaria, Norte',
                'bedrooms' => 0, 'bathrooms' => 0, 'area_m2' => 250, 'parking_spaces' => 0,
                'sector_id' => 2, 'property_type_id' => 3, 'operation_id' => 1,
                'status' => 'available', 'is_featured' => false, 'published_at' => now()->subDays(8),
                'latitude' => -1.6620, 'longitude' => -78.6400,
            ],
            [
                'title' => 'Alquiler temporal en San Alfonso',
                'description' => '<p>Alquiler temporal o por temporada. Casa acogedora con 3 dormitorios, cocina equipada, jardín y parqueadero. Mínimo 3 meses. Agua y luz incluidos. Perfecto para profesionales o familia pequeña.</p>',
                'price' => 400, 'address' => 'San Alfonso, calle Las Acacias',
                'bedrooms' => 3, 'bathrooms' => 2, 'area_m2' => 130, 'parking_spaces' => 1,
                'sector_id' => 8, 'property_type_id' => 1, 'operation_id' => 2,
                'status' => 'available', 'is_featured' => false, 'published_at' => now()->subDays(6),
                'latitude' => -1.6810, 'longitude' => -78.6560,
            ],
            [
                'title' => 'Casa residencial en Lizarzaburu',
                'description' => '<p>Casa residencial de lujo en Lizarzaburu. Acabados de primera, porcelanato, cocina integral, baños de lujo, closets empotrados. Sala cine, cuarto de estudio, jardín con césped y sistema de riego. Cerca de la ESPOCH y centros comerciales.</p>',
                'price' => 220000, 'address' => 'Av. Canónigo Ramos y Chile, Lizarzaburu',
                'bedrooms' => 4, 'bathrooms' => 3, 'area_m2' => 220, 'parking_spaces' => 2,
                'sector_id' => 4, 'property_type_id' => 1, 'operation_id' => 1,
                'status' => 'available', 'is_featured' => true, 'published_at' => now()->subDays(1),
                'latitude' => -1.6670, 'longitude' => -78.6490,
            ],
        ];

        foreach ($properties as $index => $data) {
            $property = Property::create($data);

            $imageCount = [3, 4, 5, 6][$index % 4];
            $seed = $index + 1;
            for ($i = 0; $i < $imageCount; $i++) {
                PropertyImage::create([
                    'property_id' => $property->id,
                    'image_path'  => "https://picsum.photos/seed/property-{$seed}-{$i}/800/600",
                    'alt_text'    => "{$property->title} - Foto " . ($i + 1),
                    'sort_order'  => $i,
                    'is_main'     => $i === 0,
                ]);
            }
        }

        $this->command->info('✓ ' . count($properties) . ' propiedades creadas');
    }
}
