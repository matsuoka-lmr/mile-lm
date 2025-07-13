<?php

namespace App\Services;

use App\Models\Device;
use App\Models\History;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Mime\Email;

class Baremail {
    public function __construct($config) {
        $this->config = $config;
        Cache::add('baremail.delivery_counter', 0, Carbon::now()->tomorrow());
    }

    public function send(Email $message) {
/*
['message' => [
            'from_email' => $email->getFrom(),
            'to' => collect($email->getTo())->map(function (Address $email) {
                return ['email' => $email->getAddress(), 'type' => 'to'];
            })->all(),
            'subject' => $email->getSubject(),
            'text' => $email->getTextBody(),
        ]]
*/
$message->getFrom();

        $delivery_id = date("Ymd").sprintf("%07d\n", Cache::increment('baremail.delivery_counter'));
        $xw = new XMLWriter();
        $xw->openMemory();
        $xw->startDocument("1.0", "UTF-8");
        $xw->startElement("mail");

            $xw->startElement("auth");
                $xw->startElement("site");
                    $xml->writeAttribute('id', $config['site_id']);
                $xw->endElement();

                $xw->startElement("service");
                    $xml->writeAttribute('id', $config['service_id']);
                $xw->endElement();

                $xw->startElement("name");
                    $xml->writeCdata($config['user_id']);
                $xw->endElement();

                $xw->startElement("pass");
                    $xml->writeCdata($config['password']);
                $xw->endElement();
            $xw->endElement();//auth

            $xw->startElement("delivery");
                $xml->writeAttribute('id', $delivery_id);

                $xw->writeElement("action", "reserve");
                $xw->writeElement("request_id", $config['sl_no'].'-'.$delivery_id);

                $xw->startElement("setting");
                    $xw->writeElement("send_date", "now");
                    $xw->startElement("from_name");
                        $xml->writeCdata($from_name);
                    $xw->endElement();//from_name
                    $xw->writeElement("from", $from);
                    $xw->writeElement("envelope_from", $envelope_from);

                    /*
                    ※このセクションは必要な場合のみ指定してください。指定ない場合はベアメールの標準設定で配信されます。
                    */
                    $xw->startElement("option");
                        $xw->writeElement("lifetime", $lifetime);
                        $xw->writeElement("retry_interval", $retry_interval);
                        $xw->writeElement("stop_time", $stop_time);
                        $xw->writeElement("start_time", $start_time);
                    $xw->endElement();//option

                $xw->endElement();//setting

                $xw->startElement("contents");
                    $xw->startElement("subject");
                        $xml->writeCdata($subject);
                    $xw->endElement();//subject

                    $xw->startElement("body");
                        $xml->writeAttribute('part', 'text');
                        $xml->writeCdata($body_part_text);
                    $xw->endElement();//body(part=text)

                    $xw->startElement("body");
                        $xml->writeAttribute('part', 'html');
                        $xml->writeCdata($body_part_html);
                    $xw->endElement();//body(part=html)

                $xw->endElement();//contents

                $xw->startElement("send_list");

                    $xw->startElement("data");

                        $xml->writeAttribute('id', $send_list_id);//1~n

                        $xw->startElement("address");
                            $xml->writeAttribute('device', '0');
                            $xml->text($address);
                        $xw->endElement();//address

                        $xw->startElement("int_txt");
                            $xml->writeAttribute('id', $int_txt_id);//1~n
                            $xml->text($int_txt_n);
                        $xw->endElement();//int_txt

                    $xw->endElement();//data

                $xw->endElement();//send_list

            $xw->endElement();//delivery end

        $xw->endElement();//mail

        $xw->endDocument();

        $resp = Http::baseUrl($config['base_url'])->withBody(
            $xw->outputMemory(), 'application/octet-stream'
        )->post('/tm/lpmail.php');
        if (!$resp->ok()) throw new ServiceException('[Baremail] Failed to send mail.');
        $xml = $resp->body();
    }
}
