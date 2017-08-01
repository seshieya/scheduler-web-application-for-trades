<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */



namespace Application\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Http\Request;

use Application\Database\ScheduleTable;
use Application\Database\ScheduleRowTable;
use Application\Database\JobTable;
use Application\Database\EmployeeTable;
use Application\Database\TradeTable;


class ScheduleController extends AbstractActionController
{
    const SECONDS_PER_DAY = 86400;
    const ROW_ID_START = 1;

    private $scheduleTable;
    private $scheduleRowTable;
    private $jobTable;
    private $employeeTable;
    private $tradeTable;


    public function __construct()
    {
        $this->scheduleTable = new ScheduleTable('scheduler', 'root', '');
        $this->scheduleRowTable = new ScheduleRowTable('scheduler', 'root', '');
        $this->jobTable = new JobTable('scheduler', 'root', '');
        $this->employeeTable = new EmployeeTable('scheduler', 'root', '');
        $this->tradeTable = new TradeTable('scheduler', 'root', '');
    }

    public function draftAction()
    {
        $post = $this->getRequest()->getPost();
        $startdate = $this->request->getPost('sc-startdate');

        $data = [];

        $jobAndRowsArray = $this->_separateJobAndRows($post);

        $startDateSeconds = $this->_getStartDateInSeconds($startdate);

        $dayInDayOutNumbers = $this->_calculateDayInDayOutAsNumbers($jobAndRowsArray['rowDayInDayOut']);

        $generatedDates= $this->_autoGenerateDatesFromNumbers($dayInDayOutNumbers, $startDateSeconds);

        $data = ['job' => $jobAndRowsArray['job'], 'rowOther' => $jobAndRowsArray['rowOther'], 'rowDayInDayOut' => $generatedDates];

        return new ViewModel($data);
    }

    public function saveAction()
    {
        //IN PROGRESS=========================

        $post = $this->getRequest()->getPost();

        $jobArray = [];
        $data = [];

        foreach($post as $key => $value) {
            if($key == 'sc-job-number') {
                $jobArray['job_id'] = $value;
                //temp emp_id for testing:
                //$jobArray['emp_id'] = 1;
            }
            if($key == 'sc-job-address') {
                $jobArray['address'] = $value;
            }
            if($key == 'sc-job-access') {
                $jobArray['access'] = $value;
            }
        }

        $this->jobTable->insertJobData($jobArray);

        $data['message'] = 'Schedule is saved';

        return new ViewModel($data);
    }

    //separate job info and rows into their own array:
    private function _separateJobAndRows($post)
    {
        $job = [];
        $rowDayInDayOut = [];
        $rowOther = [];
        $rowNum = ScheduleController::ROW_ID_START;


        //NEED TO ADD VALIDATION TO THE POST VALUES

        foreach ($post as $key => $value) {
            if(preg_match('/^sc-row([0-9]|[0-9]{2})-days$/', $key)) {
                $rowDayInDayOut[$key] = $value;
            }
            else if (strpos($key, 'sc-row' . $rowNum) !== false) {
                $rowOther[$key] = $value;
            }
            else if (strpos($key, 'sc-row' . ($rowNum + 1)) !== false) {
                $rowOther[$key] = $value;
                $rowNum++;
            }
            else {
                $job[$key] = $value;
            }
        }

        return ['job' => $job, 'rowOther' => $rowOther, 'rowDayInDayOut' => $rowDayInDayOut];
    }

    private function _getStartDateInSeconds($startdate) {
        $startdateInSeconds = strtotime($startdate);
        return $startdateInSeconds;
    }

    /*private function _getStartDateAsDayOfWeekNumber($startdate) {
        $startdateInSeconds = strtotime($startdate);
        $dayOfWeekAsNumber = date('w', $startdateInSeconds);

        return $dayOfWeekAsNumber;
    }*/

    private function _calculateDayInDayOutAsNumbers($array) {
        $rowNum = ScheduleController::ROW_ID_START;

        $dayInOutArray = [];
        $dayIn = 0;
        $dayOut = 0;

        //CODE TO CALCULATE DAY IN AND DAY OUT IN INTEGERS and push to an array
        foreach($array as $key => $daysNeeded) {
            //first row calculation:
            if(preg_match('/^sc-row1-days$/', $key)) {
                $dayIn = 0; //first day of work
                $dayOut = $dayIn + ($daysNeeded - 1);

                $dayInOutArray[$rowNum . 'dayIn'] = $dayIn;
                $dayInOutArray[$rowNum . 'dayOut'] = $dayOut;

                $rowNum++;
            }
            //all other rows calculation:
            else if(preg_match('/^sc-row([0-9]|[0-9]{2})-days$/', $key)) {
                $dayIn = $dayOut + 1;
                $dayOut = $dayIn + ($daysNeeded - 1);

                $dayInOutArray[$rowNum . 'dayIn'] = $dayIn;
                $dayInOutArray[$rowNum . 'dayOut'] = $dayOut;

                $rowNum++;

            }
        }

        return $dayInOutArray;

    }

    private function _autoGenerateDatesFromNumbers($daysArray, $startdateInSeconds) {

        $dayIncrement = 0;

        foreach($daysArray as $key => $day) {

            $newDate = $this->_calculateSeconds($day) + $startdateInSeconds + $dayIncrement;
            $dayOfWeek = date('w', $newDate);

            if($dayOfWeek == 6) {
                $dayIncrement += (ScheduleController::SECONDS_PER_DAY*2);
                $newDate = $this->_calculateSeconds($day) + $startdateInSeconds + $dayIncrement;
            }
            else if($dayOfWeek == 0) {
                $dayIncrement += ScheduleController::SECONDS_PER_DAY;
                $newDate = $this->_calculateSeconds($day) + $startdateInSeconds + $dayIncrement;
            }


            $dateString = $this->_convertToDateString($newDate);
            $daysArray[$key] = $dateString;

        }
        return $daysArray;
    }

    private function _calculateSeconds($numOfDays) {
        // days in seconds = 60 seconds * 60 minutes * 24 hours = 86400 seconds
        $secondsData = ScheduleController::SECONDS_PER_DAY * $numOfDays;
        return $secondsData;
    }

    private function _convertToDateString($time) {
        return date('D M j', $time);
    }


}