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

namespace App\Validator;

use App\Entity\Shell;
use App\Entity\ShellDamageCategory;
use App\Repository\ShellRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ShellNotDamagedValidator extends ConstraintValidator
{
    private $shellRepository;

    public function __construct(ShellRepository $shellRepository)
    {
        $this->shellRepository = $shellRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint \App\Validator\ShellAvailable */

        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof Shell) {
            throw new \Exception('The ShellAvailableValidator must be used on a Shell.');
        }

        $unavailableShells = $this->shellRepository->findDamagedShells(ShellDamageCategory::PRIORITY_HIGH, [$value]);
        if (!empty($unavailableShells)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
