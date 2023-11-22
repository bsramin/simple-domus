<?php

namespace App\Controller;

use App\Dto\SessionDto;
use App\Exception\DisconnectedException;
use App\Service\AssignmentService;
use App\Service\ProgramService;
use App\Util\DateUtil;
use App\Util\UserUtil;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class DashboardController extends BaseController
{
    #[Route(path: '/dashboard', name: 'dashboard')]
    public function dashboardPage(Request $request, ProgramService $programService, AssignmentService $assignmentService): Response
    {

        try {
            if (!UserUtil::isLogged($request->getSession())) {
                throw DisconnectedException::create();
            }

            $sessionDto = new SessionDto();
            $sessionDto->setParam($request->getSession()->get('csrf-param'));
            $sessionDto->setToken($request->getSession()->get('csrf-token'));
            $sessionDto->setCookie($request->getSession()->get('cookie'));
            $sessionDto->setStudents($request->getSession()->get('students'));

            $date = $request->get('data', date("Y-m-d"));
            $dateBefore = date("Y-m-d", strtotime($date . ' -1 day'));
            $dateAfter = date("Y-m-d", strtotime($date . ' +1 day'));
            $programs = $programService->program($sessionDto, $date);
            $assignments = $assignmentService->assignment($sessionDto, $date);

            return $this->render('dashboard.html.twig', [
                'today' => date("Y-m-d"),
                'date' => DateUtil::convertToItalianDate($date),
                'date_before' => $dateBefore,
                'date_after' => $dateAfter,
                'programs' => $programs,
                'assignments' => $assignments,
                'students' => $sessionDto->getStudents()
            ]);
        } catch (Throwable $t) {
            $this->addFlash('error', $t->getMessage());
            $request->getSession()->clear();
            return new RedirectResponse($this->generateUrl('login'));
        }
    }
}
