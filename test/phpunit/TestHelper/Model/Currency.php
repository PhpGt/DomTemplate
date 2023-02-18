<?php
namespace Gt\DomTemplate\Test\TestHelper\Model;

enum Currency {
	case USD;
	case EUR;
	case JPY;
	case GBP;
	case AUD;
	case CAD;
	case CHF;
	case CNH;
	case HKD;
	case NZD;

	public static function getSymbol(self $currency):string {
		return match($currency) {
			self::USD, self::AUD, self::CAD, self::HKD, self::NZD => "$",
			self::EUR => "€",
			self::JPY, self::CNH => "¥",
			self::GBP => "£",
			self::CHF => "fr",
		};
	}
}
