<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\ProgrammaticFoundation\Measure;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadApproachMeasureData extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $cityPool = [
            'Lyon',
            'Paris',
            'St Jean des Vignes',
            'Marseille',
            'Lyon',
            'Bordeaux',
        ];

        $contentPool = [
            '<p>Ut tortor mi, ullamcorper sed elit at, fringilla molestie leo. In efficitur arcu dui, id posuere nulla ultricies quis. Sed fringilla lac<a href="#">us sed enim vestibul</a>um, at ornare metus scelerisque. Phasellus id sagittis neque. In justo quam, placerat a pretium a, mattis et nibh. Duis consequat ac metus aliquam fermentum.</p>

<p style="text-align:center"><img alt="" src="https://picsum.photos/500/300/?blur" style="max-height:450px; max-width:450px" /></p>

<p>Maecenas finibus aliquam risus, eu lobortis magna convallis in. Cras et diam dignissim, auctor turpis tincidunt, rutrum urna. Aenean nec lacinia est. Donec ultrices ante eu interdum euismod. Ut tortor mi, ullamcorper sed elit at, fringilla molestie leo. In efficitur arcu dui, id posuere nulla ultricies quis. Sed fringilla lacus sed enim vestibulum, at ornare metus scelerisque. Phasellus id sagittis neque. In justo quam, placerat a pretium a, mattis et nibh. Duis consequat ac metus aliquam fermentum.</p>

<p>Proin eget laoreet mi, vel fermentum nulla. Mauris sit amet tortor bibendum, maximus enim eu, mollis quam. Mauris id dui condimentum, faucibus tortor nec, fringilla quam. In fringilla vestibulum justo eu auctor. Pellentesque commodo mi maximus leo scelerisque cursus. Fusce lobortis enim dui, in congue ligula commodo eu. Donec a augue et elit mattis iaculis. Maecenas ultricies molestie sem et efficitur.</p>

<p>Pellentesque convallis tempus magna, et sollicitudin odio fermentum sit amet. Vestibulum fringilla vel dui id accumsan. Pellentesque elementum bibendum tortor eu dignissim. Vestibulum consectetur eleifend elementum. Maecenas feugiat risus eget enim tempor, et dignissim diam hendrerit. Pellentesque elementum consectetur est, eu egestas augue mattis non. Ut arcu nisl, ornare et elit vitae, auctor convallis est. Curabitur congue finibus convallis.</p>
',
            '<p style="text-align:center"><img alt="" src="https://picsum.photos/800/200/" /></p>

<p>Ut tortor mi, ullamcorper sed elit at, fringilla molestie leo. In efficitur arcu dui, id posuere nulla ultricies quis. Sed fringilla lac<a href="#">us sed enim vestibul</a>um, at ornare metus scelerisque. Phasellus id sagittis neque. In justo quam, placerat a pretium a, mattis et nibh. Duis consequat ac metus aliquam fermentum.</p>

<p>Maecenas finibus aliquam risus, eu lobortis magna convallis in. Cras et diam dignissim, auctor turpis tincidunt, rutrum urna. Aenean nec lacinia est. Donec ultrices ante eu interdum euismod. Ut tortor mi, ullamcorper sed elit at, fringilla molestie leo. In efficitur arcu dui, id posuere nulla ultricies quis. Sed fringilla lacus sed enim vestibulum, at ornare metus scelerisque. Phasellus id sagittis neque. In justo quam, placerat a pretium a, mattis et nibh. Duis consequat ac metus aliquam fermentum.</p>
',
            "Où que nous mène la fragilité conjoncturelle, il serait intéressant de se remémorer l'ensemble des décisions imaginables, pour longtemps.",
        ];

        for ($i = 0; $i < 12; ++$i) {
            $measure = new Measure(
                $i + 1,
                sprintf('Mesure lorem %d', $i + 1),
                $contentPool[$i % 3],
                (0 === $i % 3),
                $cityPool[$i % 6],
                0 === $i % 10
            );
            $project1 = $this->getReference(sprintf('sub-approach-measure-project-%d', 2 * $i))->setPosition(1);
            $project2 = $this->getReference(sprintf('sub-approach-measure-project-%d', 2 * $i + 1))->setPosition(2);
            $manager->persist($project1);
            $manager->persist($project2);

            $measure
                ->addProject($project1)
                ->addProject($project2)
                ->addTag($this->getReference(sprintf('programmatic-foundation-tag-%d', $i % 6)))
            ;

            $manager->persist($measure);
            $this->addReference("sub-approach-measure-$i", $measure);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadApproachProjectData::class,
            LoadProgrammaticFoundationTagData::class,
        ];
    }
}
