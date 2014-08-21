<?php

namespace Gedmo\Tree;

use Doctrine\Common\EventManager;
use Tool\BaseTestCaseORM;
use Doctrine\Common\Util\Debug,
    Tree\Fixture\BehavioralCategory,
    Tree\Fixture\Article,
    Tree\Fixture\Comment,
    Gedmo\Translatable\TranslationListener,
    Gedmo\Translatable\Entity\Translation,
    Gedmo\Sluggable\SluggableListener,
    Doctrine\ORM\Proxy\Proxy;

/**
 * These are tests for Tree behavior
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Gedmo.Tree
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class TranslatableSluggableTreeTest extends BaseTestCaseORM
{
    const CATEGORY = "Tree\\Fixture\\BehavioralCategory";
    const ARTICLE = "Tree\\Fixture\\Article";
    const COMMENT = "Tree\\Fixture\\Comment";
    const TRANSLATION = "Gedmo\\Translatable\\Entity\\Translation";

    private $translationListener;

    protected function setUp()
    {
        parent::setUp();

        $evm = new EventManager;
        $evm->addEventSubscriber(new TreeListener);
        $this->translationListener = new TranslationListener;
        $this->translationListener->setTranslatableLocale('en_us');
        $evm->addEventSubscriber(new SluggableListener);
        $evm->addEventSubscriber($this->translationListener);

        $this->getMockSqliteEntityManager($evm);
        $this->populate();
    }

    public function testNestedBehaviors()
    {
        $vegies = $this->em->getRepository(self::CATEGORY)
            ->findOneByTitle('Vegitables');

        $childCount = $this->em->getRepository(self::CATEGORY)
            ->childCount($vegies);
        $this->assertEquals(2, $childCount);

        // test slug

        $this->assertEquals('vegitables', $vegies->getSlug());

        // run second translation test

        $this->translationListener->setTranslatableLocale('de_de');
        $vegies->setTitle('Deutschebles');
        $this->em->persist($vegies);
        $this->em->flush();
        $this->em->clear();

        $this->translationListener->setTranslatableLocale('en_us');

        $vegies = $this->em->getRepository(self::CATEGORY)
            ->find($vegies->getId());

        $translations = $this->em->getRepository(self::TRANSLATION)
            ->findTranslations($vegies);

        $this->assertEquals(2, count($translations));
        $this->assertArrayHasKey('de_de', $translations);
        $this->assertArrayHasKey('en_us', $translations);

        $this->assertArrayHasKey('title', $translations['de_de']);
        $this->assertEquals('Deutschebles', $translations['de_de']['title']);

        $this->assertArrayHasKey('slug', $translations['de_de']);
        $this->assertEquals('deutschebles', $translations['de_de']['slug']);

        $this->assertArrayHasKey('title', $translations['en_us']);
        $this->assertEquals('Vegitables', $translations['en_us']['title']);

        $this->assertArrayHasKey('slug', $translations['en_us']);
        $this->assertEquals('vegitables', $translations['en_us']['slug']);
    }

    public function testTranslations()
    {
        $this->populateDeTranslations();
        $repo = $this->em->getRepository(self::CATEGORY);
        $vegies = $repo->find(4);

        $this->assertEquals('Vegitables', $vegies->getTitle());
        $food = $vegies->getParent();
        // test if proxy triggers postLoad event
        $this->assertTrue($food instanceof Proxy);
        $this->assertEquals('Food', $food->getTitle());

        $this->em->clear();
        $this->translationListener->setTranslatableLocale('de_de');

        $vegies = $repo->find(4);
        $this->assertEquals('Gemüse', $vegies->getTitle());
        $food = $vegies->getParent();
        $this->assertTrue($food instanceof Proxy);
        $this->assertEquals('Lebensmittel', $food->getTitle());
    }

    protected function getUsedEntityFixtures()
    {
        return array(
            self::CATEGORY,
            self::ARTICLE,
            self::COMMENT,
            self::TRANSLATION
        );
    }

    private function populateDeTranslations()
    {
        $this->translationListener->setTranslatableLocale('de_de');
        $repo = $this->em->getRepository(self::CATEGORY);
        $food = $repo->findOneByTitle('Food');
        $food->setTitle('Lebensmittel');

        $vegies = $repo->findOneByTitle('Vegitables');
        $vegies->setTitle('Gemüse');

        $this->em->persist($food);
        $this->em->persist($vegies);
        $this->em->flush();
        $this->em->clear();
        $this->translationListener->setTranslatableLocale('en_us');
    }

    private function populate()
    {
        $root = new BehavioralCategory();
        $root->setTitle("Food");

        $root2 = new BehavioralCategory();
        $root2->setTitle("Sports");

        $child = new BehavioralCategory();
        $child->setTitle("Fruits");
        $child->setParent($root);

        $child2 = new BehavioralCategory();
        $child2->setTitle("Vegitables");
        $child2->setParent($root);

        $childsChild = new BehavioralCategory();
        $childsChild->setTitle("Carrots");
        $childsChild->setParent($child2);

        $potatoes = new BehavioralCategory();
        $potatoes->setTitle("Potatoes");
        $potatoes->setParent($child2);

        $this->em->persist($root);
        $this->em->persist($root2);
        $this->em->persist($child);
        $this->em->persist($child2);
        $this->em->persist($childsChild);
        $this->em->persist($potatoes);
        $this->em->flush();
        $this->em->clear();
    }
}
