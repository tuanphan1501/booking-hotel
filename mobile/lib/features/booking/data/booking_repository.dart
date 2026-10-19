import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../app/providers.dart';
import '../../../core/network/api_client.dart';
import '../domain/booking_models.dart';

/// Hotel search and booking endpoints of API v1 (MB-04 … MB-11).
///
/// Author: PH.Tuan · Created: 2026-10-19
class BookingRepository {
  /// Creates the repository.
  BookingRepository(this._apiClient);

  final ApiClient _apiClient;

  /// `GET /hotels` — hotels with availability for the stay.
  Future<List<HotelSummary>> searchHotels(StayQuery stay) async {
    final response = await _apiClient.get('/hotels', query: stay.apiQuery);
    return (response['data'] as List<dynamic>)
        .map((hotel) => HotelSummary.fromJson(hotel as Map<String, dynamic>))
        .toList();
  }

  /// `GET /hotels/{id}/rooms` — room types and rate plans with prices.
  Future<HotelRooms> hotelRooms(int hotelId, StayQuery stay) async {
    final response = await _apiClient.get('/hotels/$hotelId/rooms', query: stay.apiQuery);
    final data = response['data'] as Map<String, dynamic>;
    return HotelRooms(
      hotel: HotelSummary.fromJson(data['hotel'] as Map<String, dynamic>),
      amenities: (data['amenities'] as List<dynamic>? ?? []).cast<String>(),
      roomTypes: (data['room_types'] as List<dynamic>)
          .map((roomType) => RoomTypeOffer.fromJson(roomType as Map<String, dynamic>))
          .toList(),
    );
  }

  /// `POST /bookings` — creates a PENDING_PAYMENT booking holding the room.
  Future<Booking> createBooking({
    required RoomSelection selection,
    required String guestName,
    required String guestPhone,
    required String guestEmail,
    String? estimatedArrival,
    String? specialRequest,
  }) async {
    final response = await _apiClient.post('/bookings', body: {
      ...selection.stay.apiQuery,
      'rate_plan_id': selection.ratePlan.id,
      'guest_name': guestName,
      'guest_phone': guestPhone,
      'guest_email': guestEmail,
      if (estimatedArrival != null) 'estimated_arrival': estimatedArrival,
      if (specialRequest != null && specialRequest.isNotEmpty) 'special_request': specialRequest,
    });
    return Booking.fromJson(response['data'] as Map<String, dynamic>);
  }

  /// `POST /bookings/{code}/payments` — pays with VNPAY, MOMO or CARD.
  Future<Booking> pay(String bookingCode, String method) async {
    final response = await _apiClient.post('/bookings/$bookingCode/payments', body: {'method': method});
    return Booking.fromJson(response['data'] as Map<String, dynamic>);
  }

  /// `GET /bookings?scope=upcoming|past`.
  Future<List<Booking>> myBookings({required bool upcoming}) async {
    final response = await _apiClient.get('/bookings', query: {'scope': upcoming ? 'upcoming' : 'past'});
    return (response['data'] as List<dynamic>)
        .map((booking) => Booking.fromJson(booking as Map<String, dynamic>))
        .toList();
  }

  /// `GET /bookings/{code}`.
  Future<Booking> booking(String bookingCode) async {
    final response = await _apiClient.get('/bookings/$bookingCode');
    return Booking.fromJson(response['data'] as Map<String, dynamic>);
  }

  /// `POST /bookings/{code}/cancel`.
  Future<Booking> cancel(String bookingCode) async {
    final response = await _apiClient.post('/bookings/$bookingCode/cancel', body: <String, dynamic>{});
    return Booking.fromJson(response['data'] as Map<String, dynamic>);
  }
}

/// Booking endpoints.
final bookingRepositoryProvider = Provider<BookingRepository>((ref) => BookingRepository(ref.watch(apiClientProvider)));

/// Current search on the home screen (MB-03).
final stayQueryProvider = StateProvider<StayQuery>((ref) => StayQuery.initial());

/// Search results for a stay (MB-04).
final hotelSearchProvider = FutureProvider.autoDispose.family<List<HotelSummary>, StayQuery>(
  (ref, stay) => ref.watch(bookingRepositoryProvider).searchHotels(stay),
);

/// Hotel screen data (MB-05 / MB-06).
final hotelRoomsProvider = FutureProvider.autoDispose.family<HotelRooms, (int, StayQuery)>(
  (ref, args) => ref.watch(bookingRepositoryProvider).hotelRooms(args.$1, args.$2),
);

/// My trips (MB-10): true = upcoming, false = past & cancelled.
final myBookingsProvider = FutureProvider.autoDispose.family<List<Booking>, bool>(
  (ref, upcoming) => ref.watch(bookingRepositoryProvider).myBookings(upcoming: upcoming),
);

/// One booking (MB-08, MB-09, MB-11).
final bookingDetailProvider = FutureProvider.autoDispose.family<Booking, String>(
  (ref, code) => ref.watch(bookingRepositoryProvider).booking(code),
);
