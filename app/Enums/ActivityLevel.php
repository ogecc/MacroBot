<?php

namespace App\Enums;

enum ActivityLevel: string
{
    case Sedentary = 'sedentary';
    case LightlyActive = 'lightly_active';
    case ModeratelyActive = 'moderately_active';
    case VeryActive = 'very_active';
    case ExtremelyActive = 'extremely_active';
}
