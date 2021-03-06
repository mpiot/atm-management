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

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Annotation\Route;

class StaticController extends AbstractController
{
    /**
     * @Route("/mentions-legales", name="legal_notice")
     */
    public function legalNotice()
    {
        return $this->render('static/legal_notice.html.twig');
    }

    /**
     * @Route("/release-notes", name="release_notes")
     */
    public function releaseNotes(string $projectDir)
    {
        $finder = new Finder();
        $finder->in($projectDir)->files()->depth('== 0')->name('changelog.json');
        $releases = json_decode(array_values(iterator_to_array($finder))[0]->getContents(), true);

        return $this->render('static/release_notes.html.twig', [
            'releases' => $releases,
        ]);
    }
}
