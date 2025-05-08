<?php
namespace App\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use App\Enum\StatutEnum;

class EnumType extends Type
{
    const ENUM_NAME = 'statut_enum';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return "ENUM('en_attente', 'valide', 'refuse')";
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        return $value !== null ? StatutEnum::from($value) : null;
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        return $value instanceof StatutEnum ? $value->value : null;
    }

    public function getName(): string
    {
        return self::ENUM_NAME;
    }
}
