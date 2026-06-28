<?php

namespace App\Traits;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

trait LogsActivityWithDescriptions
{
    use LogsActivity;

    /**
     * Atributos a registrar en el log (sobrescribir en cada modelo).
     */
    protected function getActivitylogAttributes(): array
    {
        // Por defecto usa todos los fillable (excluyendo campos sensibles)
        return $this->fillable ?? [];
    }

    /**
     * Mapa de descripciones amigables para cada atributo.
     * Ej: ['name' => 'Nombre', 'email' => 'Correo']
     */
    protected function getActivityDescriptions(): array
    {
        return [];
    }

    /**
     * Orden de prioridad para revisar cambios.
     * Los primeros atributos tienen prioridad.
     */
    protected function getActivityPriority(): array
    {
        return $this->getActivitylogAttributes();
    }

    /**
     * Etiqueta para mostrar en las descripciones (ej. name, full_name, etc.)
     * Retorna null para usar un identificador genérico.
     */
    protected function getActivityLabel(): ?string
    {
        return $this->name ?? null;
    }

    /**
     * Configuración principal de Activity Log.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->getActivitylogAttributes())
            ->logOnlyDirty()
            ->setDescriptionForEvent(function (string $eventName) {
                $label = $this->getActivityLabel() ?: class_basename($this) . ' #' . $this->getKey();

                switch ($eventName) {
                    case 'created':
                        return "{$label} fue creado";

                    case 'updated':
                        return $this->buildUpdateDescription($label);

                    case 'deleted':
                        return "{$label} fue eliminado";

                    case 'restored':
                        return "{$label} fue restaurado";

                    default:
                        return "{$label} - {$eventName}";
                }
            })
            ->dontSubmitEmptyLogs()
            ->dontLogIfAttributesChangedOnly(['updated_at', 'created_at']);
    }

    /**
     * Construye la descripción para actualizaciones (detecta cambios y prioridad).
     */
    private function buildUpdateDescription(string $label): string
    {
        // Obtener los cambios que ya se guardaron
        $changes = $this->getChanges();
        // Remover campos de sistema que no queremos considerar
        unset($changes['updated_at'], $changes['created_at']);

        $descriptions = $this->getActivityDescriptions();
        $priority = $this->getActivityPriority();

        // Si no hay cambios relevantes, descripción genérica
        if (empty($changes)) {
            return "{$label} fue actualizado";
        }

        // Caso especial: solo cambió estado (is_active o activo)
        if (count($changes) === 1 && isset($changes['is_active'])) {
            $verb = $changes['is_active'] ? 'activado' : 'desactivado';
            return "{$label} fue {$verb}";
        }
        if (count($changes) === 1 && isset($changes['activo'])) {
            $verb = $changes['activo'] ? 'activado' : 'desactivado';
            return "{$label} fue {$verb}";
        }

        // Buscar el primer campo modificado según prioridad
        foreach ($priority as $field) {
            if (array_key_exists($field, $changes) && isset($descriptions[$field])) {
                return "{$descriptions[$field]} de '{$label}' fue actualizado";
            }
        }

        // Si no hay coincidencia, descripción genérica
        return "{$label} fue actualizado";
    }
}