<?php

namespace App\Model\Question;

enum InputType: string
{
    case Checkbox = 'checkbox';
    case Radio = 'radio';
}
