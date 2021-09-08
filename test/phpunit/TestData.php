<?php
namespace Gt\DomTemplate\Test;

class TestData {
	const MUSIC = [
		"A Band From Your Childhood" => [
			"This Album is Good" => [
				"The Best Song You‘ve Ever Heard",
				"Another Cracking Tune",
				"Top Notch Music Here",
				"The Best Is Left ‘Til Last",
			],
			"Adequate Collection" => [
				"Meh",
				"‘sok",
				"Sounds Like Every Other Song",
			],
		],
		"Bongo and The Bronks" => [
			"Salad" => [
				"Tomatoes",
				"Song About Cucumber",
				"Onions Make Me Cry (but I love them)",
			],
			"Meat" => [
				"Steak",
				"Is Chicken Really a Meat?",
				"Don‘t Look in the Sausage Factory",
				"Stop Horsing Around",
			],
			"SnaxX" => [
				"Crispy Potatoes With Salt",
				"Pretzel Song",
				"Pork Scratchings Are Skin",
				"The Peanut Is Not Actually A Nut",
			],
		],
		"Crayons" => [
			"Pastel Colours" => [
				"Egg Shell",
				"Cotton",
				"Frost",
				"Periwinkle",
			],
			"Different Shades of Blue" => [
				"Cobalt",
				"Slate",
				"Indigo",
				"Teal",
			],
		]
	];

	const STUDENTS = [
		[
			"firstName" => "Freddie",
			"lastName" => "Williams",
			"modules" => [
				"Programming 1", "Networking Fundamentals", "Computational Logic"
			]
		],
		[
			"firstName" => "Melissa",
			"lastName" => "Adams",
			"modules" => [
				"Computational Mathematics", "Networks and Security", "Graphics I"
			]
		],
		[
			"firstName" => "Sofia",
			"lastName" => "Reid",
			"modules" => [
				"Databases", "Programming 2", "Networking Fundamentals"
			]
		],
	];

	const TODO_DATA = [
		[
			"id" => 100,
			"title" => "Create DOM facade",
			"completedAt" => "2021-05-18 10:03:57",
		], [
			"id" => 101,
			"title" => "Bind data to the DOM",
			"completedAt" => "2021-05-18 16:32:20",
		], [
			"id" => 102,
			"title" => "Bundle into WebEngine",
			"completedAt" => null,
		], [
			"id" => 103,
			"title" => "Release WebEngine v4",
			"completedAt" => null,
		]
	];
}
