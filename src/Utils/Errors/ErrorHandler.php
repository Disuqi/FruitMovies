<?php

namespace App\Utils\Errors;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;

class ErrorHandler
{
    private static array $errors = [];

    public static function GetAndClearErrors() : array
    {
        $errors = self::$errors;
        self::$errors = [];
        return $errors;
    }

    public static function AddFormErrors(FormInterface $form) : void
    {
        $errors = $form->getErrors(true);
        foreach($errors as $error)
        {
            self::AddError($error->getMessage());
        }
    }
    public static function AddError(string $message) : void
    {
        $error = new Error(count(self::$errors), $message);
        self::$errors[] = $error;
    }

    public static function PopError() : Error
    {
        return array_pop(self::$errors);
    }

    public static function ShiftError() : Error
    {
        return array_shift(self::$errors);
    }
}