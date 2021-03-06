<?php

/*
 * This file is part of the Certificationy library.
 *
 * (c) Vincent Composieux <vincent.composieux@gmail.com>
 * (c) Mickaël Andrieu <andrieu.travail@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Certificationy;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Parser;

class DatafileTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var array
     */
    private $files = array();

    /**
     * @var Parser $parser
     */
    private $parser;

    /**
     * Setup files
     *
     * @return void
     */
    public function setUp()
    {
        $finder = new Finder();
        $this->files = $finder->files()->in(__DIR__ . '/../assets/test-yaml-pack/data')->name('*.yml');
        $this->parser = new Parser();
    }

    /**
     * Test that all data file is in the correct format
     *
     * @return void
     */
    public function testDatafileIntegrity()
    {
        foreach ($this->files as $file) {
            /** @var SplFileInfo $file */
            $data = $this->parser->parse($file->getContents());
            $this->assertArrayHasKey(
                'category',
                $data,
                sprintf('File "%s" does not have a category', $file->getFilename())
            );

            $this->assertArrayHasKey(
                'questions',
                $data,
                sprintf('File "%s" does not have questions', $file->getFilename())
            );
        }
    }

    /**
     * Test questions have answers
     *
     * @return void
     */
    public function testQuestionsHaveAnswers()
    {
        foreach ($this->files as $file) {
            /** @var SplFileInfo $file */
            $data = $this->parser->parse($file->getContents());
            $this->assertArrayHasKey(
                'questions',
                $data,
                sprintf(
                    'File "%s" does not have questions',
                    $file->getFilename()
                )
            );
            foreach ($data['questions'] as $num => $question) {
                $this->assertArrayHasKey(
                    'question',
                    $question,
                    sprintf(
                        'File "%s" - Question number "%d" does not have a question',
                        $file->getFilename(),
                        ($num + 1)
                    )
                );

                $this->assertArrayHasKey(
                    'answers',
                    $question,
                    sprintf(
                        'File "%s" - Question number "%d" does not have any answers',
                        $file->getFilename(),
                        ($num + 1)
                    )
                );
            }
        }
    }

    /**
     * Test question answers have minimum 1 correct answer
     *
     * @return void
     */
    public function testQuestionsHaveMinimumOneCorrectAnswer()
    {
        foreach ($this->files as $file) {
            /** @var SplFileInfo $file */
            $data = $this->parser->parse($file->getContents());
            foreach ($data['questions'] as $question) {
                if (isset($question['answers'])) {
                    foreach ($question['answers'] as $num => $answer) {
                        $this->assertArrayHasKey(
                            'value',
                            $answer,
                            sprintf(
                                'Answer number "%d" in
                                question "%s" does not have a value key',
                                ($num + 1),
                                $question['question']
                            )
                        );
                        $this->assertArrayHasKey(
                            'correct',
                            $answer,
                            sprintf(
                                'Answer number "%d" in
                                question "%s" does not have a correct key',
                                ($num + 1),
                                $question['question']
                            )
                        );

                        if (isset($answer['correct'])) {
                            if ($answer['correct'] === true) {
                                continue 2;
                            }
                        }
                    }

                    $this->fail(
                        sprintf(
                            'Question "%s" does not have a correct answer',
                            $question['question']
                        )
                    );
                }
            }
        }
    }
}
