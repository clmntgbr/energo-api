<?php

namespace App\Resolver;

use App\Dto\Context;
use App\Service\ContextService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

readonly class ContextResolver implements ValueResolverInterface
{
    public function __construct(
        private SerializerInterface $serializer,
        private ContextService $contextService,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (Context::class !== $argument->getType()) {
            return;
        }

        $groupsParam = $request->query->get('serializer', 'none');
        $groups = $this->contextService->getGroups($groupsParam);

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups($groups)
            ->toArray();

        yield new Context($context);
    }
}
