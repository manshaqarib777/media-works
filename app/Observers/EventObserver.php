<?php

namespace App\Observers;

use App\Email;
use App\Event;
use Carbon\Carbon;
use Spatie\GoogleCalendar\Event as GoogleEvent;

class EventObserver
{
    /**
     * Handle the event "created" event.
     *
     * @param  \App\Event  $event
     * @return void
     */
    public static function created(Event $event)
    {

        $googleEvent = GoogleEvent::create([
            'name' => $event->name,
            'startDateTime' => $event->start_time,
            'endDateTime' => $event->end_time,
        ]);

        $emails = array_map('trim', explode(',', request()->emails));
        foreach ($emails as $value) {
            Email::create([
                'email' => $value,
                'event_id' => $event->id
            ]);
            $googleEvent->addAttendee(['email' => $value]);
        }
        $googleEvent->save();
        if($googleEvent)
        {
            $event->update(["event_id" => $googleEvent->id]);
        }
    }

    /**
     * Handle the event "updated" event.
     *
     * @param  \App\Event  $event
     * @return void
     */
    public function updated(Event $event)
    {
        $googleEvent = GoogleEvent::find($event->event_id);


        $emails = array_map('trim', explode(',', request()->emails));
        $event->emails()->delete();
        foreach ($emails as $value) {
            Email::create([
                'email' => $value,
                'event_id' => $event->id
            ]);
            $googleEvent->addAttendee(['email' => $value]);
        }
        $googleEvent->update([
            'name' => $event->name,
            'startDateTime' => $event->start_time,
            'endDateTime' => $event->end_time,
        ]);

    }

    /**
     * Handle the event "deleted" event.
     *
     * @param  \App\Event  $event
     * @return void
     */
    public function deleted(Event $event)
    {
        $event->emails()->delete();
        $googleEvent = GoogleEvent::find($event->event_id);
        $googleEvent->delete();
    }
}
