import 'package:flutter/material.dart';

/// Stay being searched: dates and adults (MB-03 search card → MB-04 → MB-07).
///
/// Author: PH.Tuan · Created: 2026-10-19
@immutable
class StayQuery {
  /// Creates a stay query.
  const StayQuery({required this.checkIn, required this.checkOut, this.adults = 2});

  /// Default search: tomorrow, 2 nights, 2 adults.
  factory StayQuery.initial() {
    final tomorrow = DateUtils.dateOnly(DateTime.now()).add(const Duration(days: 1));
    return StayQuery(checkIn: tomorrow, checkOut: tomorrow.add(const Duration(days: 2)));
  }

  /// Reads the query from route parameters `in`, `out`, `adults`; falls back to [StayQuery.initial].
  factory StayQuery.fromParameters(Map<String, String> parameters) {
    final initial = StayQuery.initial();
    final checkIn = DateTime.tryParse(parameters['in'] ?? '');
    final checkOut = DateTime.tryParse(parameters['out'] ?? '');
    if (checkIn == null || checkOut == null || !checkOut.isAfter(checkIn)) return initial;
    return StayQuery(checkIn: checkIn, checkOut: checkOut, adults: int.tryParse(parameters['adults'] ?? '') ?? 2);
  }

  final DateTime checkIn;
  final DateTime checkOut;
  final int adults;

  /// Number of nights.
  int get nights => checkOut.difference(checkIn).inDays;

  /// Query string for app routes, e.g. `in=2026-11-20&out=2026-11-22&adults=2`.
  String get routeQuery => 'in=${_ymd(checkIn)}&out=${_ymd(checkOut)}&adults=$adults';

  /// Query parameters for the API.
  Map<String, dynamic> get apiQuery => {'check_in': _ymd(checkIn), 'check_out': _ymd(checkOut), 'adults': adults};

  /// Copies with new values.
  StayQuery copyWith({DateTime? checkIn, DateTime? checkOut, int? adults}) =>
      StayQuery(checkIn: checkIn ?? this.checkIn, checkOut: checkOut ?? this.checkOut, adults: adults ?? this.adults);

  static String _ymd(DateTime date) =>
      '${date.year.toString().padLeft(4, '0')}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}';
}

/// Hotel card of the search result (MB-04).
///
/// Author: PH.Tuan · Created: 2026-10-19
class HotelSummary {
  /// Creates a hotel summary.
  const HotelSummary({
    required this.id,
    required this.name,
    required this.city,
    required this.address,
    required this.ratingAvg,
    required this.reviewCount,
    required this.roomsLeft,
    this.description,
    this.starRating,
    this.coverUrl,
    this.fromPrice,
    this.checkInTime = '14:00',
    this.checkOutTime = '12:00',
  });

  /// Parses `HotelResource`.
  factory HotelSummary.fromJson(Map<String, dynamic> json) => HotelSummary(
        id: json['id'] as int,
        name: json['name'] as String,
        city: json['city'] as String? ?? '',
        address: json['address'] as String? ?? '',
        description: json['description'] as String?,
        starRating: json['star_rating'] as int?,
        ratingAvg: (json['rating_avg'] as num?)?.toDouble() ?? 0,
        reviewCount: json['review_count'] as int? ?? 0,
        coverUrl: json['cover_url'] as String?,
        roomsLeft: json['rooms_left'] as int? ?? 0,
        fromPrice: json['from_price'] as int?,
        checkInTime: json['check_in_time'] as String? ?? '14:00',
        checkOutTime: json['check_out_time'] as String? ?? '12:00',
      );

  final int id;
  final String name;
  final String city;
  final String address;
  final String? description;
  final int? starRating;
  final double ratingAvg;
  final int reviewCount;
  final String? coverUrl;
  final int roomsLeft;

  /// Lowest price per night for the stay; null when sold out.
  final int? fromPrice;
  final String checkInTime;
  final String checkOutTime;

  /// Local illustration shown instead of the placeholder image URL (`/images/placeholders/sea.svg` → `sea`).
  String get illustration {
    final match = RegExp(r'placeholders/([a-z]+)\.svg$').firstMatch(coverUrl ?? '');
    return match?.group(1) ?? 'room';
  }
}

/// BR-03 price breakdown returned by the API.
///
/// Author: PH.Tuan · Created: 2026-10-19
class PriceQuote {
  /// Creates a quote.
  const PriceQuote({
    required this.nights,
    required this.roomAmount,
    required this.serviceCharge,
    required this.taxAmount,
    required this.totalAmount,
  });

  /// Parses `PriceQuote::toArray()` / booking `amounts`.
  factory PriceQuote.fromJson(Map<String, dynamic> json, {int? nights}) => PriceQuote(
        nights: json['nights'] as int? ?? nights ?? 1,
        roomAmount: json['room_amount'] as int,
        serviceCharge: json['service_charge'] as int,
        taxAmount: json['tax_amount'] as int,
        totalAmount: json['total_amount'] as int,
      );

  final int nights;
  final int roomAmount;
  final int serviceCharge;
  final int taxAmount;
  final int totalAmount;
}

/// Rate plan of a room type with its price for the stay (MB-06).
///
/// Author: PH.Tuan · Created: 2026-10-19
class RatePlanOffer {
  /// Creates a rate plan offer.
  const RatePlanOffer({
    required this.id,
    required this.name,
    required this.breakfastIncluded,
    required this.refundable,
    required this.pricePerNight,
    required this.quote,
  });

  /// Parses one element of `room_types[].rate_plans`.
  factory RatePlanOffer.fromJson(Map<String, dynamic> json) => RatePlanOffer(
        id: json['id'] as int,
        name: json['name'] as String,
        breakfastIncluded: json['breakfast_included'] as bool? ?? false,
        refundable: json['refundable'] as bool? ?? true,
        pricePerNight: json['price_per_night'] as int,
        quote: PriceQuote.fromJson(json['quote'] as Map<String, dynamic>),
      );

  final int id;
  final String name;
  final bool breakfastIncluded;
  final bool refundable;
  final int pricePerNight;
  final PriceQuote quote;
}

/// Room type of a hotel with availability for the stay (MB-05 / MB-06).
///
/// Author: PH.Tuan · Created: 2026-10-19
class RoomTypeOffer {
  /// Creates a room type offer.
  const RoomTypeOffer({
    required this.id,
    required this.name,
    required this.maxAdults,
    required this.roomsLeft,
    required this.ratePlans,
    this.areaM2,
    this.bedType,
    this.imageUrl,
  });

  /// Parses one element of `room_types`.
  factory RoomTypeOffer.fromJson(Map<String, dynamic> json) => RoomTypeOffer(
        id: json['id'] as int,
        name: json['name'] as String,
        areaM2: (json['area_m2'] as num?)?.toDouble(),
        bedType: json['bed_type'] as String?,
        maxAdults: json['max_adults'] as int? ?? 2,
        imageUrl: json['image_url'] as String?,
        roomsLeft: json['rooms_left'] as int? ?? 0,
        ratePlans: (json['rate_plans'] as List<dynamic>? ?? [])
            .map((plan) => RatePlanOffer.fromJson(plan as Map<String, dynamic>))
            .toList(),
      );

  final int id;
  final String name;
  final double? areaM2;
  final String? bedType;
  final int maxAdults;
  final String? imageUrl;
  final int roomsLeft;
  final List<RatePlanOffer> ratePlans;

  /// Local illustration matching the placeholder image (`suite` or `room`).
  String get illustration => (imageUrl ?? '').contains('suite') ? 'suite' : 'room';
}

/// Hotel screen data: hotel + room types for the stay.
///
/// Author: PH.Tuan · Created: 2026-10-19
class HotelRooms {
  /// Creates the hotel screen data.
  const HotelRooms({required this.hotel, required this.roomTypes, required this.amenities});

  final HotelSummary hotel;
  final List<RoomTypeOffer> roomTypes;
  final List<String> amenities;
}

/// Room chosen on MB-06, carried to the guest form (MB-07).
///
/// Author: PH.Tuan · Created: 2026-10-19
class RoomSelection {
  /// Creates a selection.
  const RoomSelection({required this.hotel, required this.roomType, required this.ratePlan, required this.stay});

  final HotelSummary hotel;
  final RoomTypeOffer roomType;
  final RatePlanOffer ratePlan;
  final StayQuery stay;
}

/// Booking statuses (Basic Design §6.1).
///
/// Author: PH.Tuan · Created: 2026-10-19
enum BookingStatus {
  pendingPayment('PENDING_PAYMENT'),
  confirmed('CONFIRMED'),
  checkedIn('CHECKED_IN'),
  checkedOut('CHECKED_OUT'),
  cancelled('CANCELLED'),
  noShow('NO_SHOW'),
  expired('EXPIRED');

  const BookingStatus(this.code);

  /// API value.
  final String code;

  /// Parses the API value.
  static BookingStatus fromCode(String code) =>
      BookingStatus.values.firstWhere((status) => status.code == code, orElse: () => BookingStatus.confirmed);
}

/// Booking of the signed-in customer (MB-08 … MB-11).
///
/// Author: PH.Tuan · Created: 2026-10-19
class Booking {
  /// Creates a booking.
  const Booking({
    required this.code,
    required this.status,
    required this.statusLabel,
    required this.hotelName,
    required this.checkIn,
    required this.checkOut,
    required this.nights,
    required this.adults,
    required this.guestName,
    required this.guestEmail,
    required this.guestPhone,
    required this.roomTypeName,
    required this.amounts,
    required this.paidAmount,
    required this.outstandingAmount,
    required this.cancelFee,
    required this.canCancel,
    this.hotelId,
    this.hotelCity,
    this.hotelCoverUrl,
    this.ratePlanName,
    this.roomNumber,
    this.estimatedArrival,
    this.holdExpiresAt,
  });

  /// Parses `BookingResource`.
  factory Booking.fromJson(Map<String, dynamic> json) {
    final hotel = json['hotel'] as Map<String, dynamic>? ?? {};
    final rooms = json['rooms'] as List<dynamic>? ?? [];
    final firstRoom = rooms.isEmpty ? <String, dynamic>{} : rooms.first as Map<String, dynamic>;
    final amounts = json['amounts'] as Map<String, dynamic>;

    return Booking(
      code: json['booking_code'] as String,
      status: BookingStatus.fromCode(json['status'] as String),
      statusLabel: json['status_label'] as String? ?? '',
      hotelId: hotel['id'] as int?,
      hotelName: hotel['name'] as String? ?? '',
      hotelCity: hotel['city'] as String?,
      hotelCoverUrl: hotel['cover_url'] as String?,
      checkIn: DateTime.parse(json['check_in'] as String),
      checkOut: DateTime.parse(json['check_out'] as String),
      nights: json['nights'] as int,
      adults: json['adults'] as int,
      guestName: json['guest_name'] as String,
      guestEmail: json['guest_email'] as String,
      guestPhone: json['guest_phone'] as String,
      estimatedArrival: json['estimated_arrival'] as String?,
      roomTypeName: firstRoom['room_type'] as String? ?? '',
      ratePlanName: firstRoom['rate_plan'] as String?,
      roomNumber: firstRoom['room_number'] as String?,
      amounts: PriceQuote.fromJson(amounts, nights: json['nights'] as int),
      paidAmount: amounts['paid_amount'] as int,
      outstandingAmount: amounts['outstanding_amount'] as int,
      cancelFee: amounts['cancel_fee'] as int? ?? 0,
      canCancel: json['can_cancel'] as bool? ?? false,
      holdExpiresAt: DateTime.tryParse(json['hold_expires_at'] as String? ?? '')?.toLocal(),
    );
  }

  final String code;
  final BookingStatus status;
  final String statusLabel;
  final int? hotelId;
  final String hotelName;
  final String? hotelCity;
  final String? hotelCoverUrl;
  final DateTime checkIn;
  final DateTime checkOut;
  final int nights;
  final int adults;
  final String guestName;
  final String guestEmail;
  final String guestPhone;
  final String? estimatedArrival;
  final String roomTypeName;
  final String? ratePlanName;
  final String? roomNumber;
  final PriceQuote amounts;
  final int paidAmount;
  final int outstandingAmount;
  final int cancelFee;
  final bool canCancel;
  final DateTime? holdExpiresAt;

  /// Local illustration of the hotel cover.
  String get illustration {
    final match = RegExp(r'placeholders/([a-z]+)\.svg$').firstMatch(hotelCoverUrl ?? '');
    return match?.group(1) ?? 'room';
  }
}
