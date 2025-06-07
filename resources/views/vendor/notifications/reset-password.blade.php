<table align="center" cellpadding="0" cellspacing="0" style="width: 100%; background-color: #f0f0f0; font-family: 'Roboto', sans-serif; padding: 20px;">
    <tr>
        <td align="center">
            <table cellpadding="0" cellspacing="0" style="width: 600px; max-width: 600px; background-color: #ffffff; box-shadow: 0 3px 15px rgba(30,32,37,0.06); border-radius: 7px;">
                <tr>
                    <td style="padding: 20px; text-align: center;">
                        <img src="{{ URL::asset('build/images/logo-dark.png') }}" alt="Shoprite Logo" height="23" style="display: inline-block;">
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px; text-align: center; font-size: 24px; font-weight: 500; color: #495057;">
                        @if (!empty($greeting))
                            {{ $greeting }}
                        @else
                            @if ($level === 'error')
                                @lang('Whoops!')
                            @else
                                @lang('Hello!')
                            @endif
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="padding: 20px; text-align: center; color: #878a99; font-size: 15px;">
                        <p>{{ $introLines }}</p>
                    </td>
                </tr>
                @isset($actionText)
                <tr>
                    <td align="center" style="padding: 20px;">
                        <table cellpadding="0" cellspacing="0" style="background-color: #405189; border-radius: 4px;">
                            <tr>
                                <td align="center" style="padding: 10px 20px;">
                                    <a href="{{ $actionUrl }}" style="font-size: 14px; color: #FFF; text-decoration: none; font-weight: 400;">
                                        {{ $actionText }}
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                @endisset
                <tr>
                    <td style="padding: 10px; text-align: center; color: #878a99; font-size: 14px;">
                        <p>Or reset password using this link:</p>
                        <a href="{{ $actionUrl }}" target="_blank" style="color: #405189;">{{ $displayableActionUrl }}</a>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 20px; text-align: center; font-size: 14px; color: #878a99;">
                        Need Help? <br>
                        Please send any feedback or bug info to <a href="mailto:help@shoprite.co.za" style="color: #405189; font-weight: 500;">help@shoprite.co.za</a>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 20px; text-align: center; font-size: 12px; color: #98a6ad;">
                        2024 {{ config('app.name') }}. Crafted by OTB Group
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>