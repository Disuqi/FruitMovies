<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("score", IntegerType::class,
                [
                    "attr" => ["min" => 0, "max" => 10, "value"=>$options["score"]]
                ])
            ->add("comment", TextareaType::class, [
                "data" => $options["comment"],
                "attr" => ["rows" => 5]
            ])
            ->add("submit", SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => Review::class,
            "score" => "",
            "comment"=> "",
        ]);
    }
}
