<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Repositories\EventRepository;
use Core\Http\Request;
use Core\Http\Response;

class HomeController extends BaseController
{
    private readonly EventRepository $eventRepo;

    public function __construct()
    {
        $this->eventRepo = new EventRepository();
    }

    public function index(Request $request): Response
    {
        // Simply render preview.html directly - no database needed
        $viewFile = APP_PATH . '/Views/frontend/home/index.php';
        
        ob_start();
        include $viewFile;
        $content = ob_get_clean();
        
        return Response::make($content);
    }

    public function events(Request $request): Response
    {
        $events = $this->eventRepo->allPublished();

        return $this->view('frontend.events.index', [
            'events'    => $events,
            'pageTitle' => 'Upcoming Webcasts',
        ]);
    }
}
