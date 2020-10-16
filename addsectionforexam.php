<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Admin-only code to delete a course utterly.
 *
 * @package core_course
 * @copyright 2002 onwards Martin Dougiamas (http://dougiamas.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php'); //pour ajouter des modules.


//on récupere le cours a partir de l'id.
$id = required_param('id', PARAM_INT); // Course ID.
$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
$coursecontext = context_course::instance($course->id);

$PAGE->set_context($coursecontext);

//On verifie que l'utilisateur est connecté et a bien le droit de modifier le cours
require_login();
require_capability('moodle/course:update', context_course::instance($course->id));
require_capability('moodle/course:movesections', context_course::instance($course->id));

//il faut activé le suivi d'achevement d'activité dans le cours si ce n'est pas deja actif
if(!$course->enablecompletion)
{
    $course->enablecompletion=1;
    update_course($course);
}

//On ajoute une section 
$section=1; //la section en 1ere position dans le cours.
course_create_section($course, $section, $skipcheck = false);

//on ajoute une étiquette pour le text de loi des exams
$modulename="label";//Pour ajouter un lien.

$moduleinfo = new stdClass();
$moduleinfo->modulename = $modulename;
$module= $DB->get_record('modules', array('name'=>$modulename), '*', MUST_EXIST);
$moduleinfo->module=$module->id;
$moduleinfo->section = $section; // This is the section number in the course. Not the section id in the database.
$moduleinfo->course = $course->id;
$moduleinfo->visible = true;

$text=file_get_contents(__DIR__ .'/textforexam.html');;
$moduleinfo->intro =$text;
$moduleinfo->introformat = "1"; //1 pour html sinon cela propose un menu déroulant
$moduleinfo->completion = "1"; //1 pour activé la complétion d'activité au clique sur la case

$moduleinfo = add_moduleinfo($moduleinfo, $course, null);


//on ajoute un module page pour le texte de conservation des données
$modulename="page";//Pour ajouter une page.

$moduleinfo = new stdClass();
$moduleinfo->modulename = $modulename;
$module= $DB->get_record('modules', array('name'=>$modulename), '*', MUST_EXIST);
$moduleinfo->module=$module->id;
$moduleinfo->section = $section; // This is the section number in the course. Not the section id in the database.
$moduleinfo->course = $course->id;
$moduleinfo->visible = true;

$moduleinfo->display = 3;  // Display normal link in new window define('RESOURCELIB_DISPLAY_NEW', 3); 

$moduleinfo->printheading=1; // 1 pour afficher le nom de la page (mais je vois pas ce que cela fait ..)
$moduleinfo->printintro=0; // 0 pour ne pas afficher la description (on a rien mis..)

$moduleinfo->name="Mentions relatives au traitement des données à caractère personnels"; // ce qui s'affiche sur la page.
$text=file_get_contents(__DIR__ .'/textfordata.html');
$moduleinfo->introformat="1"; //1 pour html sinon cela propose un menu déroulant
$moduleinfo->content=$text;
$moduleinfo->contentformat="1"; //1 pour html sinon cela propose un menu déroulant
$moduleinfo->completion = "2"; //2 pour activé la complétion d'activité lorsque les conditions sont remplis 
$moduleinfo->completionview = "1"; //1 pour activé la complétion d'activité a la vue de la page

$moduleinfo = add_moduleinfo($moduleinfo, $course, null);


//on ajoute un depot de carte d'identité dans la section
$modulename="assign";//Pour ajouter un depot de devoir.

$moduleinfo = new stdClass();
$moduleinfo->modulename = $modulename;
$module= $DB->get_record('modules', array('name'=>$modulename), '*', MUST_EXIST);
$moduleinfo->module=$module->id;
$moduleinfo->section = $section; // This is the section number in the course. Not the section id in the database.
$moduleinfo->course = $course->id;
$moduleinfo->visible = true;

$moduleinfo->name="Dépôt pièce d'identité";

$moduleinfo->intro ="<p>Déposez ici une copie de votre carte d'identité ou passeport.</p>   ";
$moduleinfo->introformat = 1; //1 pour html sinon cela propose un menu déroulant
$moduleinfo->showdescription    = 1; //pour afficher la description sur la page de base

$moduleinfo->assignsubmission_file_enabled=1;
$moduleinfo->assignsubmission_file_maxfiles=2;
$moduleinfo->assignsubmission_file_maxsizebytes=10*1024*1024;

//$moduleinfo->assignfeedback_comments_enabled=1; //si je les mets j'obtiens une erreur bizarre comme quoi il n'y a pas de cmidnumber mais vu que je sais pas ce que c'est ^^ :-/ 
//$moduleinfo->assignfeedback_editpdf_enabled=1;

$moduleinfo->submissiondrafts=0; // Exiger que les étudiants cliquent sur le bouton « Envoyer le devoir » Aide sur Exiger que les étudiants cliquent sur le bouton « Envoyer le devoir » ? 
$moduleinfo->requiresubmissionstatement=0; // Demander aux étudiants d'accepter la déclaration de remise pour tous les devoirs Aide sur Demander aux étudiants d'accepter la déclaration de remise pour tous les devoirs ? 
$moduleinfo->sendnotifications=0;
$moduleinfo->sendstudentnotifications=0;
$moduleinfo->sendlatenotifications=0;
$moduleinfo->duedate=0;
$moduleinfo->cutoffdate=0;
$moduleinfo->gradingduedate=0;
$moduleinfo->allowsubmissionsfromdate=0;
$moduleinfo->grade=0;
$moduleinfo->teamsubmission=0;
$moduleinfo->requireallteammemberssubmit=0;
$moduleinfo->blindmarking=0;
$moduleinfo->markingworkflow=0;
$moduleinfo->markingallocation=0;

$moduleinfo->use_compilatio=0;

$moduleinfo->completion = "2"; //2 pour activé la complétion d'activité lorsque les conditions sont remplis 
$moduleinfo->completionview = "1"; //1 pour activé la complétion d'activité a la vue de la page
$moduleinfo->completionsubmit = "1"; //1 pour activé la complétion a la suite d'un depot de fichier

$moduleinfo = add_moduleinfo($moduleinfo, $course, null);

$return=new moodle_url('/course/view.php', array('id' => $id));
redirect($return);