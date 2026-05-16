<?php

namespace App\Enums;

enum Goal: string
{
    case Lose = 'lose';
    case Maintain = 'maintain';
    case Gain = 'gain';
}
