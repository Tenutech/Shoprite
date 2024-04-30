<?php

namespace App\Services\DataService;

use App\Models\ChatMonthlyData;

use Carbon\Carbon;

class ChatDataService
{
    /**
     * Fetch outgoing data for a given date range
     *
     * This method retrieves the total number of incoming and outgoing chats for a given time
     *
     * @param \DateTimeInterface|string $startDate The start date of the time frame.
     * @param \DateTimeInterface|string $endDate The end date of the time frame.
     *
     * @return \Illuminate\Support\Collection
     *     A collection containing the rejected applicants for the current year.
     */
    public function getOutgoingChat($startDate, $endDate)
    {   
        return $this->getMonthlyDataForTimeFrame('Outgoing', $startDate, $endDate)->get(['month', 'count']);
    }

    /**
     * Fetch incoming data for a given date range
     *
     * This method retrieves the total number of incoming and outgoing chats for a given time
     *
     * @param \DateTimeInterface|string $startDate The start date of the time frame.
     * @param \DateTimeInterface|string $endDate The end date of the time frame.
     *
     * @return \Illuminate\Support\Collection
     *     A collection containing the rejected applicants for the current year.
     */
    public function getIncomingChat($startDate, $endDate)
    {
        return $this->getMonthlyDataForTimeFrame('Incoming', $startDate, $endDate)->get(['month', 'count']);
    }

    /**
     * Fetch monthly data for a specific time frame.
     *
     * This method retrieves the monthly data for a specified time frame
     * from the monthly data associated with the given category type and year.
     *
     * @param string $chatType The chat type of the data.
     * @param \DateTimeInterface|string $startDate The start date of the time frame.
     * @param \DateTimeInterface|string $endDate The end date of the time frame.
     *
     * @return \Illuminate\Support\Collection
     *     A collection containing the monthly data for the specified time frame, category type, and year.
     */
    protected function getMonthlyDataForTimeFrame($chatType, $startDate, $endDate)
    {
        $startOfDay = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        return ChatMonthlyData::where('chat_type', $chatType)
            ->where('created_at', '>=', $startOfDay)
            ->where('created_at', '<=', $endDate)
            ->orderBy('created_at', 'asc');
    }
}