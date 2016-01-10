<?php namespace Bm\Field\Classes;

use Str;
use Db;
use Bm\Field\Models\Post;

class Importer extends ImporterBase
{

public static function run()
{


// komunikaty 5270 9351
// wiadomości 10
// wiadomosci z pomorza 4136
// Regulaminy rozgrywek 127
// Regulaminy rozgrywek 2452
// Regulaminy Pomorskiego ZPN 5234
// Regulaminy 2014/15 6926
// Regulaminy Piłka Kobieca 2014/15 7249
ini_set('max_execution_time', 1200);
$aktualnosci = [
    0 => 7,
    1 => 18,
    2 => 19,
    3 => 20,
];
$aktualnosci_z_pomorza = [
    0 => 17,
    1 => 21,
    2 => 22,
    3 => 23,
];
$tags = [
    1 => 18,
    2 => 19,
    3 => 20,
];
$komisje = [
    82 => 53,
    //"Zarząd"
    83 => 16,
    //"Komisja Rewizijna"
    2447 => 54,
    //"Rzecznicy związku"
    4093 => 55,
    //"Piłkarski sąd polubowny"
    1842 => 56,
    //"Komisja ds. nagłych"
    95 => 57,
    //"Komisja ds. rozgrywek"
    111 => 58,
    //"Komisaj ds. licencji"
    7277 => 59,
    //"Komisjs ds. licencji sędziowskich"
    7912 => 60,
    //"Komisja grantowa"
    96 => 61,
    //"Komisja ds. Bezpieczeństwa na Obiektach Piłkarskich"
    112 => 62,
    //"Komisja Piłkarstwa Młodzieżowego"
    97 => 63,
    //"Komisja ds. Szkolenia"
    //5606 => 64,
    //"Komisja Inwentaryzacyjna"
    7947 => 65,
    //"Komisji ds. Licencji Trenerskich"
    98 => 66,
    //"Komisja Dyscyplinarna"
    2347 => 67,
    //"Komisja Futsalu i Piłki Plażowej"
    //2081 => 68,
    //"Komisja ds. Odznaczeń i Wyróżnień"
    117 => 69,
    //"Związkowa Komisja Odwoławcza"
    118 => 70,
    //"Komisja Odwoławcza ds. Licencji"
    7958 => 71,
    //"Rada trenerów"
    2006 => 72,
    //"Biuro"
];
$komunikaty_komisji = [
    1305 => 83,
    109 =>  84,
    2009 => 91,
    110 =>  86,
    1863 => 77,
    1302 => 79,
    1320 => 89,
    1324 => 88,
    108 =>  82,
    2350 => 87,
    101 =>  78,
    2451 => 75,
    1216 => 73,
    92 =>   74,
    7930 => 81,
];
$komunikaty_id = array_flip($komunikaty_komisji);
$wydzialy = [
  19 => 73,
  2 => 74,
  53 => 75,
  // => 76,
  43 => 77,
  8 => 78,
  6 => 78,
  3 => 78,
  15 => 79,
  12 => 80,
  13 => 80,
  // => 81,
  16 => 82,
  11 => 82,
  10 => 82,
  25 => 83,
  26 => 84,
  // => 85,
  4 => 86,
  7 => 86,
  9 => 86,
  18 => 87,
  52 => 87,
  19 => 88,
  24 => 89,
  38 => 90,
  21 => 91,
  22 => 91,
  23 => 91,
];
/*
25  Komisja Piłkarstwa Młodzieżowego - od 01.01.2015 – do komunikatów przy zakładce komisji
26  Komisja Szkoleniowa Kolegium Sędziów - od 01.01.2015 – do komunikatów przy zakładce komisji
27  Komisja Kwalifikacji Kolegium Sędziów - od 01.01.2015 – do komunikatów przy zakładce komisji
28  Komisja Mentorska – Kolegium Sędziów - od 01.01.2015 – do komunikatów przy zakładce komisji
30  OSSM Gdańsk - od 01.01.2015 – do komunikatów przy zakładce komisji
31  Kadra im. K. Deyny od 01.06.2015 – do komunikatów przy zakładce komisji
33  Kadra im. K. Górskiego 01.06.2015 – do komunikatów przy zakładce komisji
34* Kadra im. W. Smolarka - od 01.06.2015 – do komunikatów przy zakładce komisji
35  Kadra Juniorek U-13 01.06.2015 – do komunikatów przy zakładce komisji
36  Kadra Młodziczek U-16 01.06.2015 – do komunikatów przy zakładce komisji
38  Rada Trenerów 01.01.2015 – do komunikatów przy zakładce komisji
39  Komisja Odznaczeń i Wyróżnień 01.01.2015 – do komunikatów przy zakładce komisji
41  PZPN - 01.01.2015 – do komunikatów przy zakładce komisji
42  Extranet - 01.01.2014 – do komunikatów przy zakładce komisji
43  Komisja ds. Nagłych 01.01.2015 – do komunikatów przy zakładce komisji
45  Z podwórka na stadion - 01.08.2014 – do komunikatów przy zakładce komisji
47  Komunikaty obsad 01.07.2015 – do komunikatów przy zakładce komisji
48  OSSM Malbork 01.01.2015 – do komunikatów przy zakładce komisji
49  Warsztaty i szkolenia - 01.07.2014 – do komunikatów przy zakładce komisji
50  Rozgrywki Junior E i F - 01.07.2015 – do komunikatów przy zakładce komisji
52  Komisja Piłki Plażowej – PRZENIEŚĆ DO FUTSALU (od stycznia 2015)
57  Fair_play - 01.01.2015 – do komunikatów przy zakładce biuro Gdańsk
58  Facebook 01.01.2015 – do komunikatów przy zakładce biuro Gdańsk
*/
$dzialy = [
  3 => 57,
  4 => 66,
  5 => 63,
  6 => 136,
  7 => 137,
  8 => 134,
  9 => 135,
  10 => 61,
  11 => 61,
  16 => 61,
  14 => 147,
  15 => 58,
  17 => 69,
  18 => 67,
  21 => 72,
  22 => 18,
  23 => 20,
  24 => 70,
  25 => 62,
  26 => 145,
  //27 => 144,
  28 => 141,
  30 => 123,
  31 => 118,
  33 => 119,
  34 => 120,
  35 => 121,
  36 => 122,
  38 => 71,
  //39 => 142,
  41 => 72,
  42 => 72,
  43 => 56,
  45 => 38,
  47 => 127,
  48 => 124,
  49 => 45,
  50 => 72,
  52 => 67,
  57 => 72,
  58 => 72,
];

$daty = [
  31 => '2015-06-01 00:00:00',
  33 => '2015-06-01 00:00:00',
  34 => '2015-06-01 00:00:00',
  35 => '2015-06-01 00:00:00',
  36 => '2015-06-01 00:00:00',
  42 => '2014-06-01 00:00:00',
  45 => '2014-08-01 00:00:00',
  47 => '2015-07-01 00:00:00',
  49 => '2014-07-01 00:00:00',
  50 => '2015-07-01 00:00:00',
];

Post::extend(function($model){
  //$model->guarded = ['id'];
  $model->throwOnValidation = false;
  $model->rules['title'] = '';//'unique:rainlab_blog_posts,title';
  $model->rules['url'] = '';
  $model->rules['slug'] = ['required', 'regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i', 'unique:rainlab_blog_posts,slug'];
});
$i = 0;
DB::rollBack();
DB::beginTransaction();
try {
    foreach (ImporterBase::$pzpn_site_content as $content) {
        $date = '2015-01-01 00:00:00';

        if (empty($content['wydzialid']) === false && isset($daty[$content['wydzialid']])) {
          $date = $daty[$content['wydzialid']];
        }

        if (
            $content['isfolder'] === '0'
            && date('Y-m-d H:i:s', $content['publishedon']) >= $date
            && in_array($content['parent'], [10])
            && empty($content['alias']) === false
        ) {
            $category = isset($dzialy[$content['wydzialid']])
              ? $dzialy[$content['wydzialid']]
              : (
                isset($aktualnosci[$content['podokreg_id']])
                  ? $aktualnosci[$content['podokreg_id']]
                  : 17
              );
            $html = str_replace('assets/', '/storage/app/media/', $content['content']);
            $data = [
                'user_id' => 2,
                'title' => $content['pagetitle'],
                'slug' => Str::slug($content['alias']),
                'excerpt' => str_replace('assets/', '/storage/app/media/', $content['introtext']),
                'content' => $html,
                'published_at' => date('Y-m-d H:i:s', $content['publishedon']),
                'published' => $content['published'] === '1' && $content['deleted'] === '0',
                'created_at' => date('Y-m-d H:i:s', $content['createdon']),
                'updated_at' => date('Y-m-d H:i:s'),
                'content_html' => '',
                'template_id' => 1,
                'category_id' => $category,//$aktualnosci[$content['podokreg_id']],//$komunikaty_komisji[$content['parent']],
                /*'additional' => empty($content['wydzialid'])
                  ? null
                  : ['collection' => $content['wydzialid']],*/
            ];

            $model_post = new Post();
            $model_post->fill($data);

            if (!$model_post->validate()) {
              //dd($model_post->errors());
              echo 'Błąd - ' . $content['id'], '<br>';
              continue;
            } else {
              $data['url'] = $model_post->getUrl();
              $post = $model_post->insertGetId($data);

              if ($post > 0 && $content['podokreg_id'] > 0) {
                  $tag = Db::insert(Db::raw('insert into bedard_blogtags_post_tag values('.(int)$tags[$content['podokreg_id']].','.$post.')'));
              }

              echo $content['id'], '<br>';
            }
        }
    }
} catch (Exception $e) {
    DB::rollBack();
    dd($e);
}
DB::commit();
echo 'Sukces!';
exit;
array(
    'id' => '9423',
    'type' => 'document',
    'contentType' => 'text/html',
    'pagetitle' => 'Uchwała nr VIII/124 w sprawie statusu zawodników oraz zasad zmian przynależności klubowej ',
    'longtitle' => '',
    'description' => 'assets/files/A_2015/Uchwaly/Uchwala nr VIII-124.pdf',
    'alias' => 'uchwała-nr-viii124-w-sprawie-statusu-zawodników-oraz-zasad-zmian-przynależności-klubowej1',
    'link_attributes' => '',
    'published' => '1',
    'pub_date' => '0',
    'unpub_date' => '0',
    'parent' => '9411',
    'isfolder' => '0',
    'introtext' => '',
    'content' => '<p>Uchwała nr VIII/124 w sprawie statusu zawodnik&oacute;w oraz zasad zmian przynależności klubowej .pdf</p>',
    'richtext' => '1',
    'template' => '5',
    'menuindex' => '12',
    'searchable' => '1',
    'cacheable' => '0',
    'createdby' => '1',
    'createdon' => '1438635743',
    'editedby' => '1',
    'editedon' => '1438636045',
    'deleted' => '0',
    'deletedon' => '0',
    'deletedby' => '0',
    'publishedon' => '1438635855',
    'publishedby' => '1',
    'menutitle' => '',
    'donthit' => '0',
    'haskeywords' => '0',
    'hasmetatags' => '0',
    'privateweb' => '0',
    'privatemgr' => '0',
    'content_dispo' => '0',
    'hidemenu' => '1',
    'podokreg_id' => '0',
    'wydzialid' => '0',
    'slideron' => '0',
    'czy_wazna' => '0'
);
}
}

/*
1   Prezydium Zarządu Pomorskiego Związku Piłki Nożnej
2   Zarząd Pomorskiego Związku Piłki Nożnej
3   Komisja ds. Rozgrywek
4   Komisja Dyscyplinarna
5   Wydział Szkolenia
6   Komisja ds. Rozgrywek
7   Komisja Dyscyplinarna
8   Komisja ds. Rozgrywek
9   Komisja Dyscyplinarna
10  Komisja ds. Bezpieczeństwa na Obiektach Piłkarskic...
11  Komisja ds. Bezpieczeństwa na Obiektach Piłkarskic...
12  Komisja Sędziowska
13  Komisja Sędziowska
14  Zarząd Kolegium Sędziów
15  Komisja ds Licencji Klubowych
16  Komisja ds. Bezpieczeństwa na Obiektach Piłkarskic...
17  Związkowa Komisja Odwoławcza
18  Komisja ds. Futsalu
19  Komisja Rewizyjna
20  Sąd Koleżeński
21  Biuro - Gdańsk
22  Biuro - Malbork
23  Biuro - Słupsk
24  Komisja Odwoławcza ds. Licencji
25  Komisja Piłkarstwa Młodzieżowego
26  Komisja Szkoleniowa
27  Komisja Kwalifikacji
28  Komisja Mentorska
29  Wiadomości o Euro
30  OSSM Gdańsk
31  Kadra im. K. Deyny
32  Kadra im. W. Kuchara
33  Kadra im. K. Górskiego
34  Kadra im. J. Michałowicza
35  Kadra Juniorek
36  Kadra Młodziczek
37  Komisja Piłkarstwa Kobiecego
38  Rada Trenerów
39  Komisja Odznaczeń i Wyróżnień
40  Żałoba
41  PZPN
42  Extranet 
43  Komisja ds. Nagłych
44  Turniej Marka Wielgusa 
45  Z podwórka na stadion
47  Komunikaty obsad 
48  OSSM Malbork 
49  Warsztaty i szkolenia
50  Rozgrywki Junior E i F 
51  Terminarz Junior E i F 
52  Komisja Piłki Plażowej 
53  Rzecznicy Związku
54  Klubowe Mistrzostwa Polski Juniorów Starszych
55  Eliminacje do Mistrzostw Europy U-19 turniej ELITE...
56  Futsal_PZPN
57  Fair_play
58  Facebook
 */