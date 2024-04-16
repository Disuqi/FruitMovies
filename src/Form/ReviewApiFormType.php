<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *      schema="NewReview",
 *      required={"score", "comment", "user_id", "movie_id"},
 *      @OA\Property(type="integer", property="score", example="5"),
 *      @OA\Property(type="string", property="comment", example="Great Movie"),
 *      @OA\Property(type="integer", property="movie_id", example="20")
 * )
 */
class ReviewApiFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("score", IntegerType::class,
                [
                    "attr" => ["min" => 0, "max" => 10]
                ])
            ->add("comment", TextType::class)
            ->add("user_id", IntegerType::class, ["mapped" => false])
            ->add("movie_id", IntegerType::class, ["mapped" => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => Review::class,
            "csrf_protection" => false,
        ]);
    }
}