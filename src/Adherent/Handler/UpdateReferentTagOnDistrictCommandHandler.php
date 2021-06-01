<?php

namespace App\Adherent\Handler;

use App\Adherent\Command\UpdateReferentTagOnDistrictCommand;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UpdateReferentTagOnDistrictCommandHandler implements MessageHandlerInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function __invoke(UpdateReferentTagOnDistrictCommand $command): void
    {
        $connection = $this->em->getConnection();

        $adherentId = $command->getAdherentId();

        $districtTags = array_column($connection->executeQuery(
            'SELECT 
                d.referent_tag_id 
            FROM adherent_referent_tag AS art
            INNER JOIN districts AS d ON d.referent_tag_id = art.referent_tag_id
            WHERE art.adherent_id = ?',
            [$adherentId]
        )->fetchAll(), 'referent_tag_id');

        $newDistrictTags = array_column($connection->executeQuery(
            'SELECT d.referent_tag_id FROM adherents AS a
            INNER JOIN geo_data AS geo ON ST_Within(ST_GeomFromText(CONCAT(\'POINT (\', a.address_longitude, \' \', a.address_latitude, \')\')), geo.geo_shape) = true
            INNER JOIN districts AS d on d.geo_data_id = geo.id
            WHERE a.id = ?',
            [$adherentId]
        )->fetchAll(), 'referent_tag_id');

        // Remove old tag
        foreach (array_diff($districtTags, $newDistrictTags) as $refTag) {
            $connection->executeQuery(
                'DELETE FROM adherent_referent_tag WHERE adherent_id = :adherent_id AND referent_tag_id = :tag_id',
                ['adherent_id' => $adherentId, 'tag_id' => $refTag]
            );
        }

        // Add new tag
        foreach (array_diff($newDistrictTags, $districtTags) as $refTag) {
            $connection->executeQuery(
                'INSERT INTO adherent_referent_tag(adherent_id, referent_tag_id) VALUES (:adherent_id, :tag_id)',
                ['adherent_id' => $adherentId, 'tag_id' => $refTag]
            );
        }
    }
}
