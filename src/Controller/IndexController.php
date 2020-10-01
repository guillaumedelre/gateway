<?php

namespace App\Controller;

use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/{arg}", name="index")
     */
    public function __invoke(Request $request, $arg)
    {
        return new Response(Status::OK, ['content-type' => 'text/html'], $arg);
    }
}
