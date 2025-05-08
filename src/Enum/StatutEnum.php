<?php
namespace App\Enum;

enum StatutEnum: string
{
    case EN_ATTENTE = 'en_attente';
    case VALIDE = 'valide';
    case REFUSE = 'refuse';

    public static function getChoices(): array
    {
        return [
            self::EN_ATTENTE->value => 'en_attente',
            self::VALIDE->value => 'valide',
            self::REFUSE->value => 'refuse',
        ];
    }

    public function getName(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'en_attente',
            self::VALIDE => 'valide',
            self::REFUSE => 'refuse',
        };
    }
}
