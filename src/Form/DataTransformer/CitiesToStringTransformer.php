<?php

namespace App\Form\DataTransformer;

use App\Entity\Cities;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CitiesToStringTransformer implements DataTransformerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Transforms an object (city) to a string (name).
     *
     * @param  Cities|null $city
     */
    public function transform($city): string
    {
        if (null === $city) {
            return '';
        }

        return $city->getName();
    }

    /**
     * Transforms a string to an object (city).
     *
     * @param  string $cityString
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($cityString): ?Cities
    {
        // no issue number? It's optional, so that's ok
        if (!$cityString) {
            return null;
        }

        $city = $this->entityManager
            ->getRepository(Cities::class)
            // query for the city with this string
            ->findOneBy(['name'=>$cityString])
        ;

        if (null === $city) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'City "%s" does not exist!',
                $cityString
            ));
        }

        return $city;
    }
}