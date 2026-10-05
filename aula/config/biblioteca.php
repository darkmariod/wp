<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Disco de la Biblioteca
    |--------------------------------------------------------------------------
    |
    | Disco privado donde viven los archivos y las miniaturas. El código lo
    | resuelve SIEMPRE con config('biblioteca.disk'), nunca por nombre fijo,
    | para poder pasar a s3 / R2 solo cambiando BIBLIOTECA_DISK en el .env.
    |
    */

    'disk' => env('BIBLIOTECA_DISK', 'biblioteca'),

    /*
    |--------------------------------------------------------------------------
    | Tope global de subida (KB)
    |--------------------------------------------------------------------------
    |
    | Opcional. Si se define, ningún tipo puede superarlo: sirve para bajar
    | los límites sin desplegar. Nunca sube el máximo propio de un tipo.
    | Recuerda que PHP (upload_max_filesize, post_max_size) y el servidor web
    | (client_max_body_size) imponen sus propios límites por encima de este.
    |
    */

    'max_upload_kb' => env('BIBLIOTECA_MAX_UPLOAD_KB'),

    /*
    |--------------------------------------------------------------------------
    | Extensiones permitidas y su contenido real
    |--------------------------------------------------------------------------
    |
    | extensión => tipos MIME que se aceptan al leer el CONTENIDO del archivo
    | (finfo), nunca el tipo que declara el navegador. No se acepta
    | application/octet-stream ni nada activo (php, html, svg, js, exe...).
    |
    | - Los formatos Office modernos (docx, xlsx, pptx, odt, ods, odp) son un
    |   ZIP por dentro y finfo a veces los reporta como application/zip.
    | - Los antiguos (doc, xls, ppt) son contenedores OLE: finfo informa su
    |   tipo propio o uno genérico de contenedor, según la versión de libmagic.
    |
    */

    'extensions' => [
        'pdf' => ['application/pdf'],

        'doc' => ['application/msword', 'application/CDFV2', 'application/x-ole-storage', 'application/vnd.ms-office'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        'odt' => ['application/vnd.oasis.opendocument.text', 'application/zip'],
        'rtf' => ['text/rtf', 'application/rtf'],
        'txt' => ['text/plain'],

        'xls' => ['application/vnd.ms-excel', 'application/CDFV2', 'application/x-ole-storage', 'application/vnd.ms-office'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
        'ods' => ['application/vnd.oasis.opendocument.spreadsheet', 'application/zip'],
        'csv' => ['text/csv', 'application/csv', 'text/plain'],

        'ppt' => ['application/vnd.ms-powerpoint', 'application/CDFV2', 'application/x-ole-storage', 'application/vnd.ms-office'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip'],
        'odp' => ['application/vnd.oasis.opendocument.presentation', 'application/zip'],

        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
        'gif' => ['image/gif'],

        'mp4' => ['video/mp4', 'video/x-m4v'],
        'webm' => ['video/webm'],
        'mov' => ['video/quicktime'],

        'mp3' => ['audio/mpeg'],
        'wav' => ['audio/x-wav', 'audio/wav', 'audio/vnd.wave'],
        'ogg' => ['audio/ogg', 'video/ogg', 'application/ogg'],
        'm4a' => ['audio/x-m4a', 'audio/mp4'],

        'zip' => ['application/zip', 'application/x-zip-compressed'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Política por tipo de recurso
    |--------------------------------------------------------------------------
    |
    | Clave = valor de App\Enums\ResourceType. El tipo "link" no lleva archivo.
    | Cada extensión debe existir en 'extensions'. max_kb está en kilobytes.
    |
    */

    'types' => [
        'pdf' => ['extensions' => ['pdf'], 'max_kb' => 51200],
        'document' => ['extensions' => ['doc', 'docx', 'odt', 'rtf', 'txt', 'xls', 'xlsx', 'ods', 'csv'], 'max_kb' => 25600],
        'presentation' => ['extensions' => ['ppt', 'pptx', 'odp'], 'max_kb' => 51200],
        'video' => ['extensions' => ['mp4', 'webm', 'mov'], 'max_kb' => 204800],
        'audio' => ['extensions' => ['mp3', 'wav', 'ogg', 'm4a'], 'max_kb' => 51200],
        'image' => ['extensions' => ['jpg', 'jpeg', 'png', 'webp', 'gif'], 'max_kb' => 10240],
        'file' => [
            'extensions' => ['pdf', 'doc', 'docx', 'odt', 'rtf', 'txt', 'xls', 'xlsx', 'ods', 'csv', 'ppt', 'pptx', 'odp', 'zip'],
            'max_kb' => 51200,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Miniaturas
    |--------------------------------------------------------------------------
    */

    'thumbnail' => [
        'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
        'max_kb' => 5120,
    ],

];
