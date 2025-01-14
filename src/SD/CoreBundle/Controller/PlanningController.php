<?php
// src/SD/CoreBundle/Controller/PlanningController.php

namespace SD\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use SD\CoreBundle\Entity\UserContext;
use SD\CoreBundle\Entity\ListContext;
use SD\CoreBundle\Entity\Planification;
use SD\CoreBundle\Entity\PlanificationPeriod;
use SD\CoreBundle\Entity\Ddate;

use SD\CoreBundle\Form\DdateType;

use SD\CoreBundle\Api\PlanningApi;
use SD\CoreBundle\Api\BookingApi;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class PlanningController extends Controller
{
    public function accesAction()
    {
    $connectedUser = $this->getUser();
    $em = $this->getDoctrine()->getManager();
    $userContext = new UserContext($em, $connectedUser); // contexte utilisateur

    $planificationRepository = $em->getRepository('SDCoreBundle:Planification');

	$currentDate = date("Ymd");

    $planifications = $planificationRepository->getPlanningPlanifications($userContext->getCurrentFile(), new \DateTime());

	// Aucune planification
	if (count($planifications) <= 0) {
		return $this->redirectToRoute('sd_core_planning_noplanification');
    }

	// Acces au planning d'une planification
	if (count($planifications) > 1) {
		return $this->redirectToRoute('sd_core_planning_many_timetable_pp', array('planificationID' => $planifications[0]['ID'], 'planificationPeriodID' => $planifications[0]['planificationPeriodID'], 'date' => $currentDate));
	} else {
		return $this->redirectToRoute('sd_core_planning_one_timetable_pp', array('planificationID' => $planifications[0]['ID'], 'planificationPeriodID' => $planifications[0]['planificationPeriodID'], 'date' => $currentDate));
	}
	}

    public function noplanificationAction()
    {
    $connectedUser = $this->getUser();
    $em = $this->getDoctrine()->getManager();
    $userContext = new UserContext($em, $connectedUser); // contexte utilisateur

    return $this->render('SDCoreBundle:Planning:noplanification.html.twig', array('userContext' => $userContext));
	}

	public function all_booking_listAction($pageNumber)
	{
	$connectedUser = $this->getUser();
	$em = $this->getDoctrine()->getManager();
	
	$userContext = new UserContext($em, $connectedUser); // contexte utilisateur

    $bRepository = $em->getRepository('SDCoreBundle:Booking');
    $numberRecords = $bRepository->getAllBookingsCount($userContext->getCurrentFile());

    $listContext = new ListContext($em, $connectedUser, 'core', 'booking', $pageNumber, $numberRecords);
    $listBookings = $bRepository->getAllBookings($userContext->getCurrentFile(), $listContext->getFirstRecordIndex(), $listContext->getMaxRecords());
                
	$planning_path = 'sd_core_planning_one_timetable'; // La route du planning est "one" ou "many" selon le nombre de planifications actives à la date du jour
	$numberPlanifications = PlanningApi::getNumberOfPlanifications($em, $userContext->getCurrentFile());
	if ($numberPlanifications > 1) {
		$planning_path = 'sd_core_planning_many_timetable';
	}

    return $this->render('SDCoreBundle:Planning:booking.list.html.twig',
		array('userContext' => $userContext, 'listContext' => $listContext, 'listBookings' => $listBookings, 'list_path' => 'sd_core_planning_all_booking_list', 'planning_path' => $planning_path));
    }

	public function current_user_booking_listAction($pageNumber)
	{
	$connectedUser = $this->getUser();
	$em = $this->getDoctrine()->getManager();
	
	$userContext = new UserContext($em, $connectedUser); // contexte utilisateur

    $bRepository = $em->getRepository('SDCoreBundle:Booking');
    $numberRecords = $bRepository->getUserFileBookingsCount($userContext->getCurrentFile(), $userContext->getCurrentUserFile());

    $listContext = new ListContext($em, $connectedUser, 'core', 'booking', $pageNumber, $numberRecords);
    $listBookings = $bRepository->getUserFileBookings($userContext->getCurrentFile(), $userContext->getCurrentUserFile(), $listContext->getFirstRecordIndex(), $listContext->getMaxRecords());
                
	$planning_path = 'sd_core_planning_one_timetable'; // La route du planning est "one" ou "many" selon le nombre de planifications actives à la date du jour
	$numberPlanifications = PlanningApi::getNumberOfPlanifications($em, $userContext->getCurrentFile());
	if ($numberPlanifications > 1) {
		$planning_path = 'sd_core_planning_many_timetable';
	}

    return $this->render('SDCoreBundle:Planning:booking.list.html.twig',
		array('userContext' => $userContext, 'listContext' => $listContext, 'listBookings' => $listBookings, 'list_path' => 'sd_core_planning_all_booking_list', 'planning_path' => $planning_path));
    }

	public function in_progress_booking_listAction($pageNumber)
	{
	$connectedUser = $this->getUser();
	$em = $this->getDoctrine()->getManager();
	
	$userContext = new UserContext($em, $connectedUser); // contexte utilisateur

    $bRepository = $em->getRepository('SDCoreBundle:Booking');
    $numberRecords = $bRepository->getFromDatetimeBookingsCount($userContext->getCurrentFile(), new \DateTime());

    $listContext = new ListContext($em, $connectedUser, 'core', 'booking', $pageNumber, $numberRecords);
    $listBookings = $bRepository->getFromDatetimeBookings($userContext->getCurrentFile(), new \DateTime(), $listContext->getFirstRecordIndex(), $listContext->getMaxRecords());
                
	$planning_path = 'sd_core_planning_one_timetable'; // La route du planning est "one" ou "many" selon le nombre de planifications actives à la date du jour
	$numberPlanifications = PlanningApi::getNumberOfPlanifications($em, $userContext->getCurrentFile());
	if ($numberPlanifications > 1) {
		$planning_path = 'sd_core_planning_many_timetable';
	}

    return $this->render('SDCoreBundle:Planning:booking.list.html.twig',
		array('userContext' => $userContext, 'listContext' => $listContext, 'listBookings' => $listBookings, 'list_path' => 'sd_core_planning_all_booking_list', 'planning_path' => $planning_path));
    }

	public function current_user_in_progress_booking_listAction($pageNumber)
	{
	$connectedUser = $this->getUser();
	$em = $this->getDoctrine()->getManager();
	
	$userContext = new UserContext($em, $connectedUser); // contexte utilisateur

    $bRepository = $em->getRepository('SDCoreBundle:Booking');
    $numberRecords = $bRepository->getUserFileFromDatetimeBookingsCount($userContext->getCurrentFile(), $userContext->getCurrentUserFile(), new \DateTime());

    $listContext = new ListContext($em, $connectedUser, 'core', 'booking', $pageNumber, $numberRecords);
    $listBookings = $bRepository->getUserFileFromDatetimeBookings($userContext->getCurrentFile(), $userContext->getCurrentUserFile(), new \DateTime(), $listContext->getFirstRecordIndex(), $listContext->getMaxRecords());
                
	$planning_path = 'sd_core_planning_one_timetable'; // La route du planning est "one" ou "many" selon le nombre de planifications actives à la date du jour
	$numberPlanifications = PlanningApi::getNumberOfPlanifications($em, $userContext->getCurrentFile());
	if ($numberPlanifications > 1) {
		$planning_path = 'sd_core_planning_many_timetable';
	}

    return $this->render('SDCoreBundle:Planning:booking.list.html.twig',
		array('userContext' => $userContext, 'listContext' => $listContext, 'listBookings' => $listBookings, 'list_path' => 'sd_core_planning_all_booking_list', 'planning_path' => $planning_path));
    }

    // Affichage du calendrier d'une planification
    /**
    * @ParamConverter("planification", options={"mapping": {"planificationID": "id"}})
	* @ParamConverter("planificationPeriod", options={"mapping": {"planificationPeriodID": "id"}})
	* @ParamConverter("date", options={"format": "Ymd"})
    */
    public function many_calendarAction(Planification $planification, PlanificationPeriod $planificationPeriod, \Datetime $date)
    {
	return PlanningController::calendarAction($planification, $planificationPeriod, $date, 1);
    }

    // Affichage du calendrier d'une planification
    /**
    * @ParamConverter("planification", options={"mapping": {"planificationID": "id"}})
	* @ParamConverter("planificationPeriod", options={"mapping": {"planificationPeriodID": "id"}})
	* @ParamConverter("date", options={"format": "Ymd"})
    */
    public function one_calendarAction(Planification $planification, PlanificationPeriod $planificationPeriod, \Datetime $date)
    {
	return PlanningController::calendarAction($planification, $planificationPeriod, $date, 0);
    }

    // Affichage du calendrier d'une planification
    public function calendarAction(Planification $planification, PlanificationPeriod $planificationPeriod, \Datetime $date, $many)
    {
    $connectedUser = $this->getUser();
    $em = $this->getDoctrine()->getManager();
    $userContext = new UserContext($em, $connectedUser); // contexte utilisateur

	PlanningApi::setCurrentCalendarPlanification($em, $connectedUser, $planification); // Le calendrier lit en base la planification courante
	PlanningApi::setCurrentCalendarMany($em, $connectedUser, $many); // Le calendrier lit en base le type d'affichage (one ou many)

    $planificationRepository = $em->getRepository('SDCoreBundle:Planification');

    $planifications = $planificationRepository->getPlanningPlanifications($userContext->getCurrentFile(), $date);

    return $this->render('SDCoreBundle:Planning:calendar.'.($many ? 'many' : 'one').'.html.twig',
		array('userContext' => $userContext, 'planifications' => $planifications, 'planification' => $planification, 'planificationPeriod' => $planificationPeriod, 'date' => $date));
    }

    // Affichage de la grille horaire journaliere d'une planification
    /**
	* @ParamConverter("planification", options={"mapping": {"planificationID": "id"}})
	* @ParamConverter("date", options={"format": "Ymd"})
	*/
    public function many_timetableAction(Planification $planification, \Datetime $date, Request $request)
    {
	return PlanningController::timetableAction($planification, $date, 1, $request);
    }

    // Affichage de la grille horaire journaliere d'une planification
    /**
	* @ParamConverter("planification", options={"mapping": {"planificationID": "id"}})
	* @ParamConverter("date", options={"format": "Ymd"})
	*/
    public function one_timetableAction(Planification $planification, \Datetime $date, Request $request)
    {
	return PlanningController::timetableAction($planification, $date, 0, $request);
    }

	// Affichage de la grille horaire journaliere d'une planification (la période de planification n'est pas passée, elle est déterminée)
    public function timetableAction(Planification $planification, \Datetime $date, $many, Request $request)
    {
    $connectedUser = $this->getUser();
    $em = $this->getDoctrine()->getManager();
    $userContext = new UserContext($em, $connectedUser); // contexte utilisateur

	// $logger = $this->get('logger'); $logger->info('DBG 1');

	$lDate = $date;

    $ddate = new Ddate();
    $form = $this->createForm(DdateType::class, $ddate);

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
		$lDate = $ddate->getDate();
	}

    $pRepository = $em->getRepository('SDCoreBundle:Planification');

	// $logger->info('DBG 3 _'.$lDate->format('Y-m-d H:i:s').'_');

    $planifications = $pRepository->getPlanningPlanifications($userContext->getCurrentFile(), $lDate);

	if (count($planifications) <= 0) {
		return $this->redirectToRoute('sd_core_planning_noplanification');
    }

	// 1) Si la planification passée n'est pas trouvée dans la liste des planifications, on prend la première de la liste
	// 2) On initialise la période planification
	$planificationFound = false;
	$planificationPeriodID = 0;
	foreach ($planifications as $i_planification) {
		if ($i_planification['ID'] == $planification->getID()) { $planificationFound = true; $planificationPeriodID = $i_planification['planificationPeriodID']; break; } // La planification en cours est dans la liste des planifications ouvertes à la date en cours.
	}

    $ppRepository = $em->getRepository('SDCoreBundle:PlanificationPeriod');

	if ($planificationFound) {
		$lPlanification = $planification;
		$planificationPeriod = $ppRepository->find($planificationPeriodID);
	} else {
		$lPlanification = $pRepository->find($planifications[0]['ID']);
		$planificationPeriod = $ppRepository->find($planifications[0]['planificationPeriodID']);
	}

	$previousDate = clone $lDate;
	$previousDate->sub(new \DateInterval('P1D'));
	$nextDate = clone $lDate;
	$nextDate->add(new \DateInterval('P1D'));

    $prRepository = $em->getRepository('SDCoreBundle:PlanificationResource');
    $planificationResources = $prRepository->getResources($planificationPeriod);

    $plRepository = $em->getRepository('SDCoreBundle:PlanificationLine');
	$planificationLine = $plRepository->findOneBy(array('planificationPeriod' => $planificationPeriod, 'weekDay' => strtoupper($lDate->format('D'))));

	if ($planificationLine === null || $planificationLine->getActive() < 1) {
		return $this->render('SDCoreBundle:Planning:timetable.'.($many ? 'many' : 'one').'.closed.html.twig',
	array('userContext' => $userContext, 'planification' => $lPlanification, 'planificationPeriod' => $planificationPeriod, 'planificationLine' => $planificationLine,
			'planifications' => $planifications, 'planificationResources' => $planificationResources,
			'date' => $lDate, 'nextDate' => $nextDate, 'previousDate' => $previousDate, 'form' => $form->createView()));
	}

    $timetableLineRepository = $em->getRepository('SDCoreBundle:TimetableLine');
    $timetableLines = $timetableLineRepository->getTimetableLines($planificationLine->getTimetable());

	$bookings = BookingApi::getTimetableBookings($em, $userContext->getCurrentFile(), $lDate, $lPlanification, $planificationPeriod, $userContext->getCurrentUserFile());


    return $this->render('SDCoreBundle:Planning:timetable.'.($many ? 'many' : 'one').'.opened.html.twig',
		array('userContext' => $userContext, 'planification' => $lPlanification, 'planificationPeriod' => $planificationPeriod, 'planificationLine' => $planificationLine,
			'planifications' => $planifications, 'planificationResources' => $planificationResources, 'timetableLines' => $timetableLines,
			'date' => $lDate, 'nextDate' => $nextDate, 'previousDate' => $previousDate, 'bookings' => $bookings, 'form' => $form->createView()));
    }


    // Affichage de la grille horaire journaliere d'une planification
    /**
	* @ParamConverter("planification", options={"mapping": {"planificationID": "id"}})
    * @ParamConverter("planificationPeriod", options={"mapping": {"planificationPeriodID": "id"}})
	* @ParamConverter("date", options={"format": "Ymd"})
	*/
    public function many_timetable_ppAction(Planification $planification, PlanificationPeriod $planificationPeriod, \Datetime $date, Request $request)
    {
	return PlanningController::timetable_ppAction($planification, $planificationPeriod, $date, 1, $request);
    }

    // Affichage de la grille horaire journaliere d'une planification
    /**
	* @ParamConverter("planification", options={"mapping": {"planificationID": "id"}})
    * @ParamConverter("planificationPeriod", options={"mapping": {"planificationPeriodID": "id"}})
	* @ParamConverter("date", options={"format": "Ymd"})
	*/
    public function one_timetable_ppAction(Planification $planification, PlanificationPeriod $planificationPeriod, \Datetime $date, Request $request)
    {
	return PlanningController::timetable_ppAction($planification, $planificationPeriod, $date, 0, $request);
    }

	// Affichage de la grille horaire journaliere d'une planification (la période de planification est passée)
    public function timetable_ppAction(Planification $planification, PlanificationPeriod $planificationPeriod, \Datetime $date, $many, Request $request)
    {
    $connectedUser = $this->getUser();
    $em = $this->getDoctrine()->getManager();
    $userContext = new UserContext($em, $connectedUser); // contexte utilisateur

	$logger = $this->get('logger');
	$logger->info('DBG 101');

    $ddate = new Ddate();
    $form = $this->createForm(DdateType::class, $ddate);

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) { // Si l'utilisateur change de date, on ré oriente vers la route qui recherche la période de planification
		return $this->redirectToRoute('sd_core_planning_'.($many ? 'many' : 'one').'_timetable', 
			array('planificationID' => $planification->getID(), 'date' => $ddate->getDate()->format("Ymd")));
	}

    $pRepository = $em->getRepository('SDCoreBundle:Planification');

    $planifications = $pRepository->getPlanningPlanifications($userContext->getCurrentFile(), $date);

	$previousDate = clone $date;
	$previousDate->sub(new \DateInterval('P1D'));
	$nextDate = clone $date;
	$nextDate->add(new \DateInterval('P1D'));

    $prRepository = $em->getRepository('SDCoreBundle:PlanificationResource');
    $planificationResources = $prRepository->getResources($planificationPeriod);

    $plRepository = $em->getRepository('SDCoreBundle:PlanificationLine');
	$planificationLine = $plRepository->findOneBy(array('planificationPeriod' => $planificationPeriod, 'weekDay' => strtoupper($date->format('D'))));

	if ($planificationLine === null || $planificationLine->getActive() < 1) {
		return $this->render('SDCoreBundle:Planning:timetable.'.($many ? 'many' : 'one').'.closed.html.twig',
	array('userContext' => $userContext, 'planification' => $planification, 'planificationPeriod' => $planificationPeriod, 'planificationLine' => $planificationLine,
			'planifications' => $planifications, 'planificationResources' => $planificationResources,
			'date' => $date, 'nextDate' => $nextDate, 'previousDate' => $previousDate, 'form' => $form->createView()));
	}

    $timetableLineRepository = $em->getRepository('SDCoreBundle:TimetableLine');
    $timetableLines = $timetableLineRepository->getTimetableLines($planificationLine->getTimetable());

	$bookings = BookingApi::getTimetableBookings($em, $userContext->getCurrentFile(), $date, $planification, $planificationPeriod, $userContext->getCurrentUserFile());


    return $this->render('SDCoreBundle:Planning:timetable.'.($many ? 'many' : 'one').'.opened.html.twig',
		array('userContext' => $userContext, 'planification' => $planification, 'planificationPeriod' => $planificationPeriod, 'planificationLine' => $planificationLine,
			'planifications' => $planifications, 'planificationResources' => $planificationResources, 'timetableLines' => $timetableLines,
			'date' => $date, 'nextDate' => $nextDate, 'previousDate' => $previousDate, 'bookings' => $bookings, 'form' => $form->createView()));
    }
}
