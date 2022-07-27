<?php

namespace App\Form;

use App\Entity\Tag;
use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Writer;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label_attr' => [
                    'class' => 'test-de-classe'
                ],
                'attr' => [
                    'class' => 'test-classe-input'
                ],
                'row_attr' => [
                    'class' => 'test-classe-div'
                ]
            ])
            ->add('body')
            ->add('published_at')
            ->add('tags', EntityType::class, [
                // looks for choices from this entity
                'class' => Tag::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.name', 'ASC');
                },
            
                // uses the User.username property as the visible option string
                'choice_label' => 'name',
                
                // used to render a select box, check boxes or radios
                'multiple' => true,
                'expanded' => true,

                'by_reference' => false,

                'attr' => [
                    // ajout d'une classe a la div des tag
                    'class' => 'checkboxes-with-scroll',
                ]
            ])
            ->add('category', EntityType::class, [
                // looks for choices from this entity
                'class' => Category::class,
            
                // uses the User.username property as the visible option string
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
                // used to render a select box, check boxes or radios
                // 'multiple' => true,
                // 'expanded' => true,
            ])
            ->add('writer', EntityType::class, [
                // looks for choices from this entity
                'class' => Writer::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('w')
                        ->join('w.user', 'u')
                        ->orderBy('u.email', 'ASC');
                },
                // uses the User.username property as the visible option string
                'choice_label' => function (Writer $object){
                    return "{$object->getUser()->getEmail()} ({$object->getUser()->getEmail()})";
                },
            
                // used to render a select box, check boxes or radios
                // 'multiple' => true,
                // 'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
