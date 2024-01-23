<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional\Ticket;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Tests\OrmFunctionalTestCase;

use function reset;

class GHXXXXXTest extends OrmFunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpEntitySchema([
            GHXXXXXSection::class,
            GHXXXXXItem::class,
        ]);
    }

    public function testProcessShouldBeUpdated(): void
    {
        $s1              = new GHXXXXXSection();
        $s1->name = 's1';

        $item1          = new GHXXXXXItem();
        $item1->section = $s1;
        $item1->value = 10;

        $item2          = new GHXXXXXItem();
        $item2->section = $s1;
        $item2->value = 20;

        $this->_em->persist($s1);
        $this->_em->persist($item1);
        $this->_em->persist($item2);
        $this->_em->flush();
        $this->_em->clear();


        $dtoClass = GHXXXXXSectionDto::class;
        $itemClass = GHXXXXXItem::class;

        $result = $this->sortByAliasedSubQuery($dtoClass, $itemClass);
        $result = $this->sortByDirectSubQuery($dtoClass, $itemClass);
        $result = $this->sortByDirectColumnNumber($dtoClass, $itemClass);
        $result = $this->sortByParametrizedColumnNumber($dtoClass, $itemClass);

        if($result === null) {
            echo "\nThere is no possible way to sort a Query Result by a SubQuery";
        } else {
            echo "\nThere is at least one possible way to sort a Query Result by a SubQuery";
        }
        self::assertNotNull($result);
        self::assertCount(1, $result);
        self::assertSame(30, $result[0]->sumItems);
    }

    /**
     * @return GHXXXXXSectionDto[]|null
     */
    private function sortByParametrizedColumnNumber(string $dtoClass, string $itemClass): ?array
    {
        $result = null;
        try {
            $qb = $this->_em->createQueryBuilder();
            /** @var GHXXXXXSectionDto[] $result */
            $result = $qb->select("new $dtoClass(s.id, s.name, (select sum(i.value) from $itemClass i where i.section = s))")
                ->from(GHXXXXXSection::class, 's')
                ->orderBy(":columnNumber", 'asc')
                ->setParameter('columnNumber', 3)
                ->getQuery()
                ->getResult();
            echo "\nAble to sort by the column number (via parameter)";
        } catch (\Throwable $t) {
            echo "\nUnable to sort by the column number (via parameter): " . $t->getMessage();
        }

        return $result;
    }

    /**
     * @return GHXXXXXSectionDto[]|null
     */
    private function sortByDirectColumnNumber(string $dtoClass, string $itemClass): ?array
    {
        $result = null;
        try {
            $qb = $this->_em->createQueryBuilder();
            /** @var GHXXXXXSectionDto[] $result */
            $result = $qb->select("new $dtoClass(s.id, s.name, (select sum(i.value) from $itemClass i where i.section = s))")
                ->from(GHXXXXXSection::class, 's')
                ->orderBy("3", 'asc')
                ->getQuery()
                ->getResult();
            echo "\nAble to sort by the column number (directly)";
        } catch (\Throwable $t) {
            echo "\nUnable to sort by the column number (directly): " . $t->getMessage();
        }

        return $result;
    }

    /**
     * @return GHXXXXXSectionDto[]|null
     */
    private function sortByDirectSubQuery(string $dtoClass, string $itemClass): ?array
    {
        $result = null;
        try {
            $qb = $this->_em->createQueryBuilder();
            /** @var GHXXXXXSectionDto[] $result */
            $result = $qb->select("new $dtoClass(s.id, s.name, (select sum(i.value) from $itemClass i where i.section = s))")
                ->from(GHXXXXXSection::class, 's')
                ->orderBy("(select sum(io.value) from $itemClass io where io.section = s)", 'asc')
                ->getQuery()
                ->getResult();
            echo "\nAble to put SubQuery directly into the orderBy() method";
        } catch (\Throwable $t) {
            echo "\nUnable to put SubQuery directly into the orderBy() method: " . $t->getMessage();
        }

        return $result;
    }

    /**
     * @return GHXXXXXSectionDto[]|null
     */
    private function sortByAliasedSubQuery(string $dtoClass, string $itemClass): ?array
    {
        $result = null;
        try {
            $qb = $this->_em->createQueryBuilder();
            /** @var GHXXXXXSectionDto[] $result */
            $result = $qb->select("new $dtoClass(s.id, s.name, (select sum(i.value) from $itemClass i where i.section = s) as sumItems)")
                ->from(GHXXXXXSection::class, 's')
                ->orderBy('sumItems', 'asc')
                ->getQuery()
                ->getResult();
            echo "\nAble to assign an alias to a SubQuery";
        } catch (\Throwable $t) {
            echo "\nUnable to assign an alias to a SubQuery: " . $t->getMessage();
        }

        return $result;
    }
}

/**
 * @ORM\Entity
 */
class GHXXXXXSection
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    public $id;

    /**
     * @ORM\Column(type="string")
     *
     * @var int
     */
    public $name;

}

/**
 * @ORM\Entity
 */
class GHXXXXXItem
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    public $id;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    public $value;

    /**
     *
     * @ORM\ManyToOne(targetEntity="GHXXXXXSection")
     *
     * @var GHXXXXXSection
     */
    public $section;
}

readonly class GHXXXXXSectionDto {


    public function __construct(
        public string $id,
        public string $name,
        public int $sumItems
    )
    {
    }
}