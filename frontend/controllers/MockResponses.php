<?php

namespace Multiple\Frontend\Controllers;

use Embed\Embed;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;
use Phalcon\Tag;
use Phalcon\Http\Response;

//use CmsV3\Common\Models\Language;
//use CmsV3\Common\Models\Website;
//use CmsV3\Common\Models\Navigation;
//use CmsV3\Common\Models\Template;

/**
 * Class to have common mock objects
 * Class MockResponses
 * @package Multiple\Frontend\Controllers
 */
class MockResponses
{

    public static function getMockDatatablesRow() {
        $dataTables = new \stdClass();

        $row = array();
        $row[] = '<span class="1">Preview</span>';
        $row[] = 'Preview';
        $row[] = false;

        $dataTables->aaData = array($row);
        return $dataTables;
    }

    public static function getMockCoregroupDetailResponse() {

        $coreGroupDetail = new \stdClass();

        $coreGroupDetail->coreGroupId = "1";
        $coreGroupDetail->coreGroupName = "Preview Core Group";
        $coreGroupDetail->coreGroupMeetingLocationName = "Location Name";
        $coreGroupDetail->coreGroupMeetingLocationPhone = "0123456789";
        $coreGroupDetail->hideUrls = true;
        $coreGroupDetail->coreGroupMeetingDay = "Monday";
        $coreGroupDetail->coreGroupMeetingTime = "12:00 AM";
        $coreGroupDetail->coreGroupMeetingLocationAddress = "";
        $coreGroupDetail->coreGroupMeetingLocationDirection = "";
        $coreGroupDetail->coreGroupLeadershipSub=[];

        return $coreGroupDetail;
    }

    /**
     * Private method to return mock event json data
     * @return string
     */
    public static function getMockEventDetailResponse(){

        $eventDetail = new \stdClass();

        $eventDetail->allowNonMembers=true;
        $eventDetail->allowOnlineRegistration=true;
        $eventDetail->allowRegistrations=true;
        $eventDetail->associatedRegions="Preview";
        $eventDetail->contactPerson="Firstname Lastname<br/>Phone No: 0123456789<br/>*<a href='#' class='iconEmail'><i class=\"fa fa-envelope-o\" data-toggle=\"tooltip\" title=\"Send Message\" aria-hidden=\"true\"></i></a>";
        $eventDetail->costForMembers="GBP 1.00";
        $eventDetail->costForNonMembers="GBP 1.00";
        $eventDetail->eventDetailHTML="";
        $eventDetail->eventEndDate="09/30/2013";
        $eventDetail->eventEndTime="11:00 AM";
        $eventDetail->eventName="Preview Event";
        $eventDetail->eventStartDate="09/30/2013";
        $eventDetail->eventStartTime="10:00 AM";
        $eventDetail->eventType="Preview Event";
        $eventDetail->location="Preview<br/>Preview";
        $eventDetail->maxNoAttendees="10";
        $eventDetail->noOfRegistrations=2;
        $eventDetail->registrationMessage="Registration Message";
        $eventDetail->registrationURLforMembers="#";
        $eventDetail->registrationURLforNonMembers="#";
        $eventDetail->registrationValid=true;
        $eventDetail->shortDescription="Preview Event";

        return json_encode($eventDetail);
    }

    /**
     * Mock json response which return and array of member detail object.
     * @return array
     */
    public static function getMockMemberDetailsResponse(){
        $jsonResponse =  [
                "addressTemplate" => "##title## ##firstName## ##lastName##<br/>##companyName##<br/>##addressLine1##<br/>##addressLine2##<br/>##postCode## ##city##<br/>##state##",
                "directorRoles" => "",
                "isDirector" => false,
                "memberAFavoriteProblemISolved" => "",
                "memberAFavoriteProblemISolvedHeading" => "",
                "memberAddressDisplay" => true,
                "memberAddressLine1" => "Salz",
                "memberAddressLine2" => "Road",
                "memberAnIdealReferral" => "",
                "memberAnIdealReferralHeading" => "",
                "memberAnIdealReferralPartnerForMe" => "",
                "memberAnIdealReferralPartnerForMeHeading" => "",
                "memberChapId" => "440&t=95ab43e8f5de45eec4b481b163c970af7a7c7a1223b277ccbeaa5e7168a62538&name=BNI+Dummy+Member+Preview",
                "memberChapter" => "Preview Chapter",
                "memberChapterText" => "Team",
                "memberChapters" => [],
                "memberCity" => "",
                "memberCompany" => "",
                "memberCompanyLogo" => "",
                "memberCompleteAddress" => "ALD Member1<br/>Salz",
                "memberCountry" => "Austria",
                "memberDirectNumber" => "",
                "memberDirectNumberDisplay" => false,
                "memberDirectNumberHeading" => "",
                "memberFaxNumber" => "",
                "memberFaxNumberDisplay" => false,
                "memberFaxNumberHeading" => "",
                "memberFirstName" => "First ",
                "memberHasCompanyLogo" => false,
                "memberHasMspImage" => false,
                "memberHasPhoto" => false,
                "memberId" => "3OW%2Bu6wgTEotDRByUJtpEA%3D%3D",
                "memberLastName" => "Last",
                "memberMobile" => "",
                "memberMobileDisplay" => false,
                "memberMobileHeading" => "",
                "memberMspImage" => "",
                "memberMyBusiness" => "",
                "memberMyBusinessHeading" => "",
                "memberMyFavoriteBNIStory" => "",
                "memberMyFavoriteBNIStoryHeading" => "",
                "memberMyTopProductsRightNow" => "",
                "memberMyTopProductsRightNowHeading" => "",
                "memberPhone" => "123",
                "memberPhoneHeading" => "Telefonnummer",
                "memberPhoneNumberDisplay" => true,
                "memberPhoto" => "",
                "memberProffession" => "Optræden",
                "memberSendMessage" => true,
                "memberSendMessageHeading" => "Send besked",
                "memberSocialMediaHeading" => "",
                "memberSpeciality" => "Nails",
                "memberState" => "",
                "memberTitle" => "",
                "memberTollFreeNumber" => "",
                "memberTollFreeNumberDisplay" => false,
                "memberTollFreeNumberHeading" => "",
                "memberWebsite" => "",
                "memberWebsiteDisplay" => false,
                "memberWebsiteHeading" => "",
                "memberZipCode" => "",
                "sub" =>[
                    [
                        "id" => 1,
                        "name" => "http://facebook.com",
                        "selected" => null,
                    ],
                    1 => [
                        "id" => 2,
                        "name" => "http://orkut.com",
                        "selected" => null,
                    ],
                    [
                        "id" => 3,
                        "name" => "http://linkedin.com",
                        "selected" => null,
                    ],
                    [
                        "id" => 4,
                        "name" => "http://twitter.com",
                        "selected" => null,
                    ],
                ],
                ];

        return $jsonResponse;
    }



    public static function setupMockChapterDetailData($view)
    {
        $view->ChapterDetails = (array)new ChapterDetail("Chapter Name", "Monday", "12:00 AM",
            "Meeting Place", "220 Address", "Address part 2", "City",
            "BNI 123", "3", "#", "GB", "#","state");

        $view->LeadershipTeam = array((array)new LeadershipTeam("-2", "President", "app.title.mr", "First", "Last", "###########", "example@example.com"),
            (array)new LeadershipTeam("-2", "Chapter Area Director", "app.title.mrs", "First", "Last", "###########", "example@example.com"));

        $view->OrgMembers = array((array)new OrgMembers("-1", "app.title.mr", "First1", "Last1",
            "###########", "example@example.com", "N/A", "Company", "Music", "Teacher"),
            (array)new OrgMembers("-1", "app.title.mrs", "First2", "Last2",
                "###########", "example@example.com", "N/A", "", "Photography", "Portrait"),
            (array)new OrgMembers("-1", "app.title.miss", "First3", "Last3",
                "###########", "example@example.com", "N/A", "BNI", "Art", "Art Sales")
        );

        $view->OrgType = "CHAPTER";
        $view->MemberCount = "15";
        return $view;
    }

}

class ChapterDetail
{
    var $name;
    var $meetingDay;
    var $meetingTime;
    var $locationName;
    var $addressLine1;
    var $addressLine2;
    var $city;
    var $postalCode;
    var $totalMemberCount;
    var $visitChapterUrl;
    var $chapterUrl;
    var $country;
    var $state;

    /**
     * ChapterDetail constructor.
     * @param $name
     * @param $meetingDay
     * @param $meetingTime
     * @param $locationName
     * @param $addressLine1
     * @param $addressLine2
     * @param $city
     * @param $postalCode
     * @param $totalMemberCount
     * @param $visitChapterUrl
     * @param $country
     * @param $chapterUrl
     */
    public function __construct($name, $meetingDay, $meetingTime, $locationName, $addressLine1, $addressLine2, $city, $postalCode, $totalMemberCount, $visitChapterUrl, $country, $chapterUrl,$state)
    {
        $this->name = $name;
        $this->meetingDay = $meetingDay;
        $this->meetingTime = $meetingTime;
        $this->locationName = $locationName;
        $this->addressLine1 = $addressLine1;
        $this->addressLine2 = $addressLine2;
        $this->city = $city;
        $this->country = $country;
        $this->postalCode = $postalCode;
        $this->totalMemberCount = $totalMemberCount;
        $this->visitChapterUrl = $visitChapterUrl;
        $this->chapterUrl = $chapterUrl;
        $this->state = $state;
    }

}

class LeadershipTeam
{
    var $userId;
    var $role;
    var $title;
    var $firstName;
    var $lastName;
    var $phone;
    var $email;

    /**
     * LeadershipTeam constructor.
     * @param $userId
     * @param $role
     * @param $title
     * @param $firstName
     * @param $lastName
     * @param $phone
     * @param $email
     */
    public function __construct($userId, $role, $title, $firstName, $lastName, $phone, $email)
    {
        $this->userId = $userId;
        $this->role = $role;
        $this->title = $title;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->phone = $phone;
        $this->email = $email;
    }

}

class OrgMembers
{
    var $userId;
    var $title;
    var $firstName;
    var $lastName;
    var $phone;
    var $email;
    var $profileUrl;
    var $companyName;
    var $profession;
    var $speciality;

    /**
     * OrgMembers constructor.
     * @param $userId
     * @param $title
     * @param $firstName
     * @param $lastName
     * @param $phone
     * @param $email
     * @param $profileUrl
     * @param $companyName
     * @param $profession
     * @param $speciality
     */
    public function __construct($userId, $title, $firstName, $lastName, $phone, $email, $profileUrl, $companyName, $profession, $speciality)
    {
        $this->userId = $userId;
        $this->title = $title;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->phone = $phone;
        $this->email = $email;
        $this->profileUrl = $profileUrl;
        $this->companyName = $companyName;
        $this->profession = $profession;
        $this->speciality = $speciality;
    }


}