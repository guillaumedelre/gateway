<?php

namespace App\Controller;

use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class OkController extends AbstractController
{
    /**
     * @Route("/200", name="ok")
     */
    public function __invoke(Request $request)
    {
        return new Response(Status::OK, ['content-types' => 'application/json'], '{}');
    }
}
