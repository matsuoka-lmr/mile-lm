{{$customer->name}} 様

{{$customer->address}}
{{$customer->phone}}

いつもお世話になっております。
{{$shop->name}}@isset($user)の{{$user->name}}@endissetです。

下記車両のメンテナンスをご案内いたしますので、
メンテナンス実施日を別途ご相談させてください。

車　　種：{{$vehicle->model}}
車両番号：{{$vehicle->number}}

【タイヤローテーション】{{$tire_notice ? '交換時期になりました。' : ''}}
前回交換日：{{$tire_date}}
交換後走行距離：{{$tire_date=='未設定' ? '-' : number_format($vehicle->tire_mileage).'Km'}}
設定サイクル：{{$tire_notice_settings}}

【タイヤ交換】{{$oil_notice ? '交換時期になりました。' : ''}}
前回交換日：{{$oil_date}}
交換後走行距離：{{$oil_date=='未設定' ? '-' : number_format($vehicle->oil_mileage).'Km'}}
設定サイクル：{{$oil_notice_settings}}

【100Km点検】{{$battery_notice ? '点検時期になりました。' : ''}}
前回点検日：{{$battery_date}}
点検後走行距離：{{$battery_date=='未設定' ? '-' : number_format($vehicle->battery_mileage).'Km'}}
設定サイクル：{{$battery_notice_settings}}

以上、よろしくお願いいたします。
―――――
{{$shop->name}}
@isset($user)
{{$user->name}}
@endisset
{{$shop->phone}}
@isset($user)
{{$user->email}}
@endisset
