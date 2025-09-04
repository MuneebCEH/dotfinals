{{-- resources/views/attendance/history.blade.php --}}
@extends('layouts.app')

@section('title', 'Attendance History')

@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-2xl font-bold mb-4">Your Attendance History</h2>

                    {{-- <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <p class="text-blue-800 dark:text-blue-200">
                            <i class="fas fa-info-circle mr-2"></i>
                            All times are displayed in Pakistan Time (UTC+5)
                        </p>
                    </div> --}}

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Date</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Check In</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Check Out</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Hours Worked</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Notes</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($attendances as $attendance)
                                    @php
                                        $checkIn = Carbon\Carbon::parse($attendance->check_in)->setTimezone(
                                            'Asia/Karachi',
                                        );
                                        $checkOut = $attendance->check_out
                                            ? Carbon\Carbon::parse($attendance->check_out)->setTimezone('Asia/Karachi')
                                            : null;
                                    @endphp
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $checkIn->format('M j, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $checkIn->format('g:i A') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $checkOut ? $checkOut->format('g:i A') : 'Not checked out' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $attendance->hours_worked ? number_format($attendance->hours_worked, 2) . ' hours' : '-' }}
                                        </td>
                                        <td class="px-6 py-4">{{ $attendance->notes ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center">No attendance records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $attendances->links() }}
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('dashboard') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
