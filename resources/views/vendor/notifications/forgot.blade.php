<div class="row">
    <div class="col-12">
        <table class="body-wrap" style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; background-color: transparent; margin: 0;">
            <tr style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                <td style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0;" valign="top"></td>
                <td class="container" width="600" style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto;" valign="top">
                    <div class="content" style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; max-width: 600px; display: block; margin: 0 auto; padding: 20px;">
                        <table class="main" width="100%" cellpadding="0" cellspacing="0" itemprop="action" itemscope itemtype="http://schema.org/ConfirmAction" style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; border-radius: 3px; margin: 0; border: none;">
                            <tr style="font-family: 'Roboto', sans-serif; font-size: 14px; margin: 0;">
                                <td class="content-wrap" style="font-family: 'Roboto', sans-serif; box-sizing: border-box; color: #495057; font-size: 14px; vertical-align: top; margin: 0;padding: 30px; box-shadow: 0 3px 15px rgba(30,32,37,.06); ;border-radius: 7px; background-color: #fff;" valign="top">
                                    <meta itemprop="name" content="Confirm Email" style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;" />
                                    <table width="100%" cellpadding="0" cellspacing="0" style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                        <tr style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                            <td class="content-block" style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
                                                <div style="margin-bottom: 15px;">
                                                    <img src="{{URL::asset('build/images/logo-dark.png')}}" alt="" height="23">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                            <td class="content-block" style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 20px; line-height: 1.5; font-weight: 500; vertical-align: top; margin: 0; padding: 0 0 10px;" valign="top">
                                                Hey, {{ $username }}
                                            </td>
                                        </tr>
                                        <tr style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                            <td class="content-block" style="font-family: 'Roboto', sans-serif; color: #878a99; box-sizing: border-box; line-height: 1.5; font-size: 15px; vertical-align: top; margin: 0; padding: 0 0 10px;" valign="top">
                                                You are receiving this email because we received a password reset 
                                                request for your account.
                                            </td>
                                        </tr>
                                        <tr style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                            <td class="content-block" style="font-family: 'Roboto', sans-serif; color: #878a99; box-sizing: border-box; line-height: 1.5; font-size: 15px; vertical-align: top; margin: 0; padding: 0 0 24px;" valign="top">
                                                If you did not request a password reset, no further action is 
                                                required.
                                            </td>
                                        </tr>
                                        <tr style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                            <td class="content-block" itemprop="handler" itemscope itemtype="http://schema.org/HttpActionHandler" style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 22px; text-align: center;" valign="top">
                                                @isset($actionText)
                                                    <a href="{{ $actionUrl }}" itemprop="url" style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: .8125rem; color: #FFF; text-decoration: none; font-weight: 400; text-align: center; cursor: pointer; display: inline-block; border-radius: .25rem; text-transform: capitalize; background-color: #405189; margin: 0; border-color: #405189; border-style: solid; border-width: 1px; padding: .5rem .9rem;">
                                                        {{ $actionText }}
                                                    </a>
                                                @endisset
                                            </td>
                                        </tr>
                                        <tr style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                            <td class="content-block" style="color: #878a99; text-align: center;font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0; padding-top: 5px" valign="top">
                                                <p style="margin-bottom: 10px;">
                                                    Or reset using this link: 
                                                </p>
                                                <a href="{{ $actionUrl }}" target="_blank">
                                                    {{ $displayableActionUrl }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr style="font-family: 'Roboto', sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                            <td class="content-block" style="font-family: 'Roboto', sans-serif; color: #878a99; box-sizing: border-box; line-height: 1.5; font-size: 15px; vertical-align: top; margin: 0; padding: 0 0 24px;" valign="top">
                                                {{ $expire }}
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <div style="text-align: center; margin: 25px auto 0px auto;font-family: 'Roboto', sans-serif;">
                            <h4 style="font-weight: 500; line-height: 1.5;font-family: 'Roboto', sans-serif;">
                                Need Help ?
                            </h4>
                            <p style="color: #878a99; line-height: 1.5;">
                                Please send and feedback or bug info to 
                                <a href="mailto:help@shoprite.co.za" style="font-weight: 500;">
                                    help@shoprite.co.za
                                </a>
                            </p>
                            <p style="font-family: 'Roboto', sans-serif; font-size: 14px;color: #98a6ad; margin: 0px;">
                                2024 {{ config('app.name') }}. Crafted by OTB Group
                            </p>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <!-- end table -->
    </div>
    <!--end col-->
</div><!-- end row -->