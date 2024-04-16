<?php

namespace App\Form;

use App\Entity\Movie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="NewMovie",
 *     required={"title", "overview"},
 *     @OA\Property(type="string", property="title", example="Example Movie Title"),
 *     @OA\Property(type="string", property="overview", example="This is an example overview"),
 *     @OA\Property(type="integer", property="running_time", example="120"),
 *     @OA\Property(type="string", property="release_date", example="dd-mm-yyyy")
 * )
 */
class MovieApiFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("title")
            ->add("overview")
            ->add("running_time", IntegerType::class,  [
                "required" => false,
                "attr" => ["min" => 0]
            ])
            ->add("release_date", TextType::class,
                [
                    "required" => false,
                    "mapped" => false,
                ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => Movie::class,
            "csrf_protection" => false,
        ]);
    }
}
