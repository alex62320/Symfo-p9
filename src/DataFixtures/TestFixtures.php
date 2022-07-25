<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Page;
use App\Entity\Tag;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use phpDocumentor\Reflection\Types\Null_;

class TestFixtures extends Fixture
{
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        $this->loadCategories($manager, $faker);
        $this->loadTags($manager, $faker);
        $this->loadArticles($manager, $faker);
        $this->loadPages($manager,$faker);
    }

    public function loadCategories(ObjectManager $manager, FakerGenerator $faker): void
    {
        $categoryNames = [
            'cuisine française',
            'cuisine italienne',
            'cuisine ukrainienne',
        ];

        foreach ($categoryNames as $categoryName) {
            $category = new Category();
            $category->setName($categoryName);
            $manager->persist($category);
        }

        for ($i = 0; $i < 10; $i++) {
            $category = new Category();
            $category->setName("cuisine {$faker->countryCode()}");
            $manager->persist($category);
        }

        $manager->flush();
    }

    public function loadTags(ObjectManager $manager, FakerGenerator $faker)
    {
        $tagNames = [
            'rapide',
            'végétarien',
            'carné',
        ];

        foreach ($tagNames as $tagName) {
            $tag = new Tag();
            $tag->setName($tagName);
            $manager->persist($tag);
        }

        for ($i = 0; $i < 10; $i++) {
            $tag = new Tag();
            $tag->setName($faker->word());
            $manager->persist($tag);
        }

        $manager->flush();
    }

    public function loadArticles(ObjectManager $manager, FakerGenerator $faker): void
    {
        $repository = $this->doctrine->getRepository(Category::class);
        $categories = $repository->findAll();

        $repository = $this->doctrine->getRepository(Tag::class);
        $tags = $repository->findAll();

        $articleDatas = [
            [
                'title' => 'Boeuf bourguignon',
                'body' => 'Un plat français typique',
                'published_at' => DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-06-30 09:00:00'),
                'category' => $categories[0],
                'tags' => [$tags[2]],
            ],
            [
                'title' => 'Spaghetti carbonara',
                'body' => 'Un plat italien typique',
                'published_at' => null,
                'category' => $categories[1],
                'tags' => [$tags[0], $tags[2]],
            ],
            [
                'title' => 'Borsh',
                'body' => 'Un plat ukrainien typique',
                'published_at' => DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-07-03 11:00:00'),
                'category' => $categories[2],
                'tags' => [$tags[2]],
            ],
        ];

        foreach ($articleDatas as $articleData) {
            $article = new Article();
            $article->setTitle($articleData['title']);
            $article->setBody($articleData['body']);
            $article->setPublishedAt($articleData['published_at']);
            $article->setCategory($articleData['category']);

            foreach ($articleData['tags'] as $tag) {
                $article->addTag($tag);
            }

            $manager->persist($article);
        }

        for ($i = 0; $i < 20;$i++) {
            $article = new Article();
            $article->setTitle($faker->sentence());
            $article->setBody($faker->paragraph(6));

            // génère une date aléatoire 90% du temps 
            // et renvoie une valeur nulle 10% du temps
            $date = $faker->optional($weight = 0.9)->dateTimeBetween('-6 month', '+6 month');
            // format : YYYY-mm-dd HH:ii:ss
            if ($date) {
                $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', "2022-{$date->format('m-d H:i:s')}");
                // si la gestion de la date est trop compliquée, voici une alternative mais l'année changera en fonction de quand vous lancer le chargement des fixtures
                // $date = $faker->dateTimeThisYear();
                // $date = DateTimeImmutable::createFromInterface($date);
            }
            
            $article->setPublishedAt($date);

            $category = $faker->randomElements($categories)[0];
            $article->setCategory($category);

            $count=random_int(0,4);
            $articleTags=$faker->randomElements($tags,$count);

            foreach($articleTags as $tag){
                $article->addTag($tag);
            }
            $manager->persist($article);
        }

        $manager->flush();
    }
    public function loadPages(ObjectManager $manager, FakerGenerator $faker): void
    {
        $repository = $this->doctrine->getRepository(Category::class);
        $categories = $repository->findAll();

        $pageDatas = [
            [
                'title' => 'La cuisine francaise',
                'body' => "C'est la cuisine de la France",
                'category' => $categories[0],
            ],
            [
                'title' => 'la cuisine italienne',
                'body' => "c'est la cuisine de l'Italie",
                'category' => $categories[1],
            ],
            [
                'title' => 'La cuisine Ukrainienne',
                'body' => "c'est la cuisine d'Ukraine",
                'category' => $categories[2],
            ],
        ];

        foreach ($pageDatas as $pageData) {
            $page = new Page();
            $page->setTitle($pageData['title']);
            $page->setBody($pageData['body']);
            $page->setCategory($pageData['category']);


            $manager->persist($page);
        }

        for ($i = 0; $i < 10;$i++) {
            $page = new Page();
            $page->setTitle($faker->sentence());
            $page->setBody($faker->paragraph(6));


            $category = $faker->randomElements($categories)[0];
            $page->setCategory($category);

            $manager->persist($page);
        }

        $manager->flush();
    }

}