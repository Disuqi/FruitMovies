<?php

namespace App\Form;

use App\Entity\Movie;
use App\Repository\CrewMemberRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class AddMovieFormType extends AbstractType
{
    private CrewMemberRepository $crewMemberRepository;
    public function __construct(CrewMemberRepository $crewMemberRepository)
    {
        $this->crewMemberRepository = $crewMemberRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $directorEntities = $this->crewMemberRepository->findBy(["role" => "Director"]);
        $actorEntities = $this->crewMemberRepository->findBy(["role" => "Actor"]);
        $actors = [];
        foreach ($actorEntities as $actor) {
            $actors[$actor->getName()] = $actor->getId();
        }
        $directors = [];
        foreach ($directorEntities as $director) {
            $directors[$director->getName()] = $director->getId();
        }
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
            ->add("director", ChoiceType::class, [
                "choices" => $directors,
                "placeholder" => "Choose a director",
                "required" => false,
            ])
            ->add("actors" , ChoiceType::class, [
                "choices" => $actors,
                "multiple" => true,
                "placeholder" => "Choose actors",
                "required" => false,
            ])
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
