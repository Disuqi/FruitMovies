<?php

namespace App\Form;

use App\Entity\Movie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class AddMovieFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("cover_photo", FileType::class, [
                "label" => null,
                "mapped" => false,
                "required" => false,
                "attr" => ["class" => "hidden", "accept" => "image/*"],
                "constraints" => [
                    new File(
                        ["mimeTypes" => [
                                "image/*"
                            ],
                            "mimeTypesMessage" => "Please upload a valid image file (png, jpg, jpeg)"])]])
            ->add("title")
            ->add("running_time", IntegerType::class,  [
                "attr" => ["min" => 0]
            ])
            ->add("overview")
            ->add("release_date")
            ->add("submit", SubmitType::class)
            ->setAction("addMovie");
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => Movie::class,
        ]);
    }
}
