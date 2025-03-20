<?php

namespace App\Services;

use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CustomPathGenerator implements PathGenerator
{
   
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media) . '/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media) . '/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media) . '/responsive-images/';
    }

    protected function getBasePath(Media $media): string
    {
        // Obtener el modelo asociado
        $model = $media->model;

        // Obtener el atributo único (reemplaza 'uuid' con el nombre de tu atributo)
        $uniqueId = $model->uuid ?? $model->id; // Usar uuid si existe, si no usar el id.

        // Generar el MD5
        return md5($uniqueId . config('app.key'));
    }
}