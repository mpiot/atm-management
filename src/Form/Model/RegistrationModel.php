<?php

/*
 * Copyright 2020 Mathieu Piot
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\Form\Model;

use App\Entity\License;
use App\Entity\MedicalCertificate;
use App\Entity\SeasonCategory;
use App\Entity\User;
use App\Validator as AppAssert;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AppAssert\UniqueUser()
 */
class RegistrationModel
{
    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    public $email;

    /**
     * @var string
     */
    public $phoneNumber;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(min="6", max="4096")
     */
    public $plainPassword;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $gender;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $firstName;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $lastName;

    /**
     * @var \DateTime
     * @Assert\NotBlank()
     */
    public $birthday;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $laneNumber;

    /**
     * @var string
     * @Assert\NotNull()
     */
    public $laneType;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $laneName;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $postalCode;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $city;

    /**
     * @var MedicalCertificate
     * @Assert\Valid()
     */
    public $medicalCertificate;

    /**
     * @var bool
     */
    public $federationEmailAllowed = false;

    /**
     * @var bool
     */
    public $clubEmailAllowed = true;

    /**
     * @var bool
     */
    public $partnersEmailAllowed = false;

    public function generateUser(SeasonCategory $seasonCategory, UserPasswordEncoderInterface $passwordEncoder)
    {
        $license = (new License($seasonCategory))
            ->setMedicalCertificate($this->medicalCertificate)
            ->setFederationEmailAllowed($this->federationEmailAllowed)
        ;

        $user = new User();
        $user
            ->setEmail($this->email)
            ->setPhoneNumber($this->phoneNumber)
            ->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $this->plainPassword
                )
            )
            ->setGender($this->gender)
            ->setFirstName($this->firstName)
            ->setLastName($this->lastName)
            ->setBirthday($this->birthday)
            ->setLaneNumber($this->laneNumber)
            ->setLaneType($this->laneType)
            ->setLaneName($this->laneName)
            ->setPostalCode($this->postalCode)
            ->setCity($this->city)
            ->addLicense($license)
            ->setClubEmailAllowed($this->clubEmailAllowed)
            ->setPartnersEmailAllowed($this->partnersEmailAllowed)
        ;

        return $user;
    }
}
