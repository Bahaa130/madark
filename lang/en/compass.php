<?php

return [
    'title' => 'Compass',
    'subtitle' => 'Assess your skills & interests to find your ideal orbit',
    'start' => 'Start assessment',
    'question' => 'Question',
    'of' => 'of',
    'result' => 'Your result',
    'recommended' => 'Recommended orbit',
    'restart' => 'Retake assessment',
    'matches' => 'Based on your answers, these journeys fit your orbit:',
    'questions' => [
        [
            'id' => 'q1',
            'text' => 'Which field sparks your curiosity right now?',
            'options' => [
                [
                    'id' => 'a',
                    'text' => 'Building digital products from scratch',
                    'tag' => 'programming',
                ],
                [
                    'id' => 'b',
                    'text' => 'Understanding data and acting on it',
                    'tag' => 'data',
                ],
                [
                    'id' => 'c',
                    'text' => 'Designing experiences people remember',
                    'tag' => 'design',
                ],
                [
                    'id' => 'd',
                    'text' => 'Running team projects and long-term planning',
                    'tag' => 'business',
                ],
            ],
        ],
        [
            'id' => 'q2',
            'text' => 'How do you prefer to learn?',
            'options' => [
                [
                    'id' => 'a',
                    'text' => 'Hands-on projects and practical problems',
                    'tag' => 'programming',
                ],
                [
                    'id' => 'b',
                    'text' => 'Analyses and real case studies',
                    'tag' => 'data',
                ],
                [
                    'id' => 'c',
                    'text' => 'Deep explanations of the underlying theory',
                    'tag' => 'design',
                ],
                [
                    'id' => 'd',
                    'text' => 'Guides and templates I can apply right away',
                    'tag' => 'business',
                ],
            ],
        ],
        [
            'id' => 'q3',
            'text' => 'How much time do you give learning each week?',
            'options' => [
                [
                    'id' => 'a',
                    'text' => 'An hour or less — I want focused lessons',
                    'tag' => 'programming',
                ],
                [
                    'id' => 'b',
                    'text' => '2 to 4 hours — a steady pace',
                    'tag' => 'data',
                ],
                [
                    'id' => 'c',
                    'text' => '4 to 8 hours — a busy schedule',
                    'tag' => 'design',
                ],
                [
                    'id' => 'd',
                    'text' => '8 hours or more — an intensive start',
                    'tag' => 'business',
                ],
            ],
        ],
        [
            'id' => 'q4',
            'text' => 'What is your main goal from this journey?',
            'options' => [
                [
                    'id' => 'a',
                    'text' => 'Building a personal project I am proud of',
                    'tag' => 'programming',
                ],
                [
                    'id' => 'b',
                    'text' => 'Landing a job that changes my career',
                    'tag' => 'business',
                ],
                [
                    'id' => 'c',
                    'text' => 'Deepening what I already know',
                    'tag' => 'data',
                ],
                [
                    'id' => 'd',
                    'text' => 'Exploring a completely new field',
                    'tag' => 'design',
                ],
            ],
        ],
    ],
];
