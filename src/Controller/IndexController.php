<?php

namespace App\Controller;

use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @Route("/", name="index")
     */
    public function __invoke(Request $request)
    {
        return new Response(
            Status::OK,
            ['content-type' => 'application/json'],
            ''
        );
    }
}
