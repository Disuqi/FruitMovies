<?php

namespace App\Form;

use App\Utils\Search\MoviesSearchOptions;
use App\Utils\Search\OrderMoviesBy;
use App\Utils\Search\SortOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderMoviesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("orderBy", ChoiceType::class,
                [
                    "label" => "Order By",
                    "choices" =>
                        [
                            "Title" => OrderMoviesBy::Title,
                            "Rating" => OrderMoviesBy::Rating,
                            "Release Date" => OrderMoviesBy::ReleaseDate,
                        ],
                    "expanded"=> false,
                    "multiple" => false,
                ])
            ->add("sortOrder", ChoiceType::class,
                [
                    "label" => null,
                    "choices" =>
                        [
                            "Ascending" => SortOrder::Ascending,
                            "Descending" => SortOrder::Descending,
                        ],
                    "expanded"=> false,
                    "multiple" => false,
                ])
            ->add("apply", SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MoviesSearchOptions::class
        ]);
    }
}
