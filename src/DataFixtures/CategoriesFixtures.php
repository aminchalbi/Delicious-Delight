<?php 
namespace App\DataFixtures;

use App\Entity\Categories;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoriesFixtures extends Fixture
{
    private $counter = 1;
    public function __construct(private SluggerInterface $slugger){}
  
    public function load(ObjectManager $manager): void
    {
        $this->createCategory('Surgelé', null, $manager);
        $this->createCategory('Dande', null, $manager);
        $this->createCategory('Poulet', null, $manager);
        $this->createCategory('Charcuterie', null, $manager);

        $manager->flush();
    }

    public function createCategory(string $name, ?Categories $parent = null, ObjectManager $manager): Categories
    {
        $category = new Categories();
        $category->setName($name);
        $category->setSlug($this->slugger->slug($category->getName())->lower());
        $category->setParent($parent);
        $manager->persist($category);
        $this->addReference('category_' . $this->counter, $category);
        $this->counter++;
        return $category;
    }
}
