<?php

namespace App\Application\CommandHandler;

use App\Dto\OpenDataPrice;
use App\Entity\Type;
use App\Repository\TypeRepository;

abstract class CreateGasPriceHandlerAbstract
{
    public function __construct(
        private TypeRepository $typeRepository,
    ) {
    }

    public function getType(OpenDataPrice $openDataPrice): Type
    {
        $type = $this->typeRepository->findOneBy(['typeId' => $openDataPrice->id]);

        if (null === $type) {
            $type = Type::createType($openDataPrice->id, $openDataPrice->name);
            $this->typeRepository->save($type, true);
        }

        return $type;
    }
}
