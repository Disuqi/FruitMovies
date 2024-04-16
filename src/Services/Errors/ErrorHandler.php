<?php

namespace App\Services\Errors;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ErrorHandler
{
    public static function GetAndClearErrors(SessionInterface $session) : array
    {
        $errors = $session->get("errors");
        if(!$errors) $errors = [];
        $session->remove("errors");
        return $errors;
    }

    public static function AddFormErrors(Session $session, FormInterface $form) : void
    {
        $formErrors = $form->getErrors(true);
        foreach($formErrors as $fromError)
        {
            self::AddError($session, $fromError->getMessage());
        }
    }

    public static function AddError(Session $session, string $message) : void
    {
        $errors = $session->get("errors");
        if(!$errors) $errors = [];
        $error = new Error(count($errors), $message);
        $errors[] = $error;
        $session->set("errors", $errors);
    }
}