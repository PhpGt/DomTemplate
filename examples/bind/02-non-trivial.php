<?php
/** @noinspection HtmlUnknownTarget */
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\Bind;
use Gt\DomTemplate\BindGetter;
use Gt\DomTemplate\DocumentBinder;

require __DIR__ . "/../../vendor/autoload.php";

// EXAMPLE CODE: https://github.com/PhpGt/DomTemplate/wiki/Binding#non-trivial-usage

$html = <<<HTML
<h1>Top three drivers of <span data-bind:text="year">0000</span></h1>

<ul>
	<li data-template>
		<h2 data-bind:text="name">Name of driver</h2>
		<h3 data-bind:text="team">Team Name</h3>

		<p>Points: <span data-bind:text="points">0</span>
		<div>
			<img src="/flag/{{countryCode}}.png" alt="Flag of {{countryName}}" />
			<p data-bind:text="countryName">Country</p>
		</div>
	</li>
</ul>
HTML;

function example(DocumentBinder $binder, DriverRepository $driverRepo):void {
	$now = new DateTime();
	$currentYear = $now->format("Y");

	// Here we're calling an example data source to get an array of Driver objects.
	$drivers = $driverRepo->getDrivers(
		year: $currentYear,
		orderBy: "points",
		limit: 3,
	);

	$binder->bindKeyValue("year", $currentYear);
	$binder->bindList($drivers);
}

// END OF EXAMPLE CODE.

$document = new HTMLDocument($html);
$binder = new DocumentBinder($document);
$driverRepo = new DriverRepository();
example($binder, $driverRepo);
$binder->cleanDatasets();
echo $document;

class DriverRepository {
	/** @return array<Driver>
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function getDrivers(
		?int $year = null,
		?string $orderBy = "name",
		?int $limit = null
	):array {
// This is a fake data source. In the real world, this function's parameters
// would be used to query the database. For this example, we're just
// hard-coding the data.
		return [
			new Driver("Lewis Hamilton", "Mercedes", 347, "United Kingdom (GBR)"),
			new Driver("Valtteri Bottas", "Mercedes", 223, "Finland (FIN)"),
			new Driver("Max Verstappen", "Red Bull Racing Honda", 214, "Netherlands (NED)"),
		];
	}
}

/**
 * This is an example of how a model object could look in your application.
 * It can represent a row in a database, with some extra custom functionality.
 * Note how the #[Bind] and #[BindGetter] attributes mark the functions as
 * containing data to bind to the document.
 */
class Driver {
	private string $name;
	private string $team;
	private int $points;
	private string $countryDetail;

	public function __construct(
		string $name,
		string $team,
		int $points,
		string $countryDetail
	) {
		$this->name = $name;
		$this->team = $team;
		$this->points = $points;
		$this->countryDetail = $countryDetail;
	}

	#[Bind("name")]
	public function getFullName():string {
		return $this->name;
	}

	#[BindGetter]
	public function getTeam():string {
		return $this->team;
	}

	#[BindGetter]
	public function getPoints():int {
		return $this->points;
	}

	#[BindGetter]
	public function getCountryName():string {
		return trim(
			strtok($this->countryDetail, "(")
		);
	}

	#[BindGetter]
	public function getCountryCode():string {
		strtok($this->countryDetail, "(");
		return trim(strtok("("), "()");
	}
}
