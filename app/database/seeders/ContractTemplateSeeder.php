<?php

namespace Database\Seeders;

use App\Models\ContractTemplate;
use Illuminate\Database\Seeder;

class ContractTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $content = <<<'HTML'
<div style="text-align: center; margin-bottom: 30px;">
    <h1 style="color: #1e40af; font-size: 24px; margin-bottom: 10px;">CONTRACT PRESTĂRI SERVICII</h1>
    <p style="font-size: 14px; color: #64748b;">Nr. {{contract_number}} din {{contract_date}}</p>
</div>

<h2 style="color: #1e40af; font-size: 16px; border-bottom: 2px solid #1e40af; padding-bottom: 5px; margin-top: 30px;">Părțile</h2>

<p><strong>S.C. SIMPLEAD MEDIA S.R.L.</strong>, persoană juridică română, cu sediul în Mun. Satu Mare, Str. Wolfenbuttel, Nr. 4-6, et. Parter, Ap. 4, Jud. Satu Mare, înregistrată la Registrul Comerțului de pe lângă Tribunalul SATU MARE sub nr. J30/606/2023, Cod Unic de Înregistrare: RO48170565, reprezentată legal de Medve Zsolt, identificat cu CI seria XX nr. 000000, eliberată de Pol. Satu Mare la data de 00.00.0000, CNP 0000000000, în calitate de <strong>Administrator</strong>, și denumită în continuare „<strong>Prestator</strong>",</p>

<p>Și</p>

<p><strong>{{client_company}}</strong>, persoană juridică română, cu sediul în {{client_address}}, înregistrată la Registrul Comerțului sub nr. {{client_registration_number}}, Cod Unic de Înregistrare: {{client_tax_id}}, reprezentată legal de {{client_name}}, în calitate de <strong>Administrator</strong>, și denumită în continuare „<strong>Beneficiar</strong>",</p>

<p>Au convenit să încheie prezentul contract de prestări servicii în următoarele condiții:</p>

<h2 style="color: #1e40af; font-size: 16px; border-bottom: 2px solid #1e40af; padding-bottom: 5px; margin-top: 30px;">Art. 1 Obiectul Contractului</h2>

<p>Prestatorul se obligă să furnizeze următoarele servicii către Beneficiar:</p>

{{SERVICES_TABLE}}

<h2 style="color: #1e40af; font-size: 16px; border-bottom: 2px solid #1e40af; padding-bottom: 5px; margin-top: 30px;">Art. 2 Durata</h2>

<p>2.1. Prezentul contract se încheie pe o perioadă de 12 luni.</p>

<p>2.2. Termenul de livrare este de 30 de zile lucrătoare (10-30 de zile lucrătoare pentru primirea tuturor feed-back-urilor pentru elementele grafice (design propunere)).</p>

<h2 style="color: #1e40af; font-size: 16px; border-bottom: 2px solid #1e40af; padding-bottom: 5px; margin-top: 30px;">Art. 3 Preț</h2>

<p>3.1. Prețul total este de <strong>{{contract_total}} {{currency}}</strong> + TVA, stabilit pe baza acordului ambelor părți la data semnării contractului.</p>

<p>3.2. Plata serviciilor se va efectua în avans, după cum urmează: 50% din valoarea contractului la semnarea acestuia, iar diferența de 50% la predarea-primirea lucrărilor/serviciilor finalizate, prin transfer bancar în contul Prestatorului.</p>

<p>3.3. Prețul pentru serviciile de mentenanță lunară este inclus în valoarea totală, pentru primul an. După această perioadă, tarifele de mentenanță pot fi renegociate.</p>

<p>3.4. În cazul neachitării sumei scadente de către Beneficiar la termenul stabilit, Prestatorul este îndreptățit să perceapă penalități de întârziere de 0.5% pe zi de întârziere din suma neachitată, până la achitarea integrală a sumei datorate, cu condiția ca penalitățile totale să nu depășească valoarea debitului principal, precum și suspendarea serviciilor până la clarificarea situației financiare.</p>

<h2 style="color: #1e40af; font-size: 16px; border-bottom: 2px solid #1e40af; padding-bottom: 5px; margin-top: 30px;">Art. 4 Drepturile și obligațiile</h2>

<h3 style="font-size: 14px; margin-top: 20px;">4.1. Prestatorul:</h3>
<ul>
    <li>Se obligă să execute serviciile solicitate conform specificațiilor agreate cu Beneficiarul;</li>
    <li>Își rezervă dreptul de a suspenda temporar serviciile în caz de neplată sau pentru întreținere sau actualizări tehnice;</li>
    <li>Va asigura securitatea datelor procesate în cadrul serviciilor;</li>
    <li>Se obligă să livreze serviciile în termenul convenit;</li>
    <li>Își rezervă dreptul de a refuza implementarea unor funcționalități care ar putea compromite securitatea sau performanța website-ului;</li>
    <li>Se obligă să păstreze confidențialitatea informațiilor primite de la Beneficiar;</li>
    <li>Va oferi suport tehnic în perioada de mentenanță;</li>
    <li>Își rezervă dreptul de a utiliza lucrările realizate în scopuri promoționale (portofoliu, prezentări), cu excepția cazului în care Beneficiarul solicită explicit altfel.</li>
</ul>

<h3 style="font-size: 14px; margin-top: 20px;">4.2. Beneficiarul:</h3>
<ul>
    <li>Se obligă să achite contravaloarea serviciilor conform termenilor stabiliți;</li>
    <li>Va furniza toate informațiile și materialele necesare realizării serviciilor;</li>
    <li>Se obligă să ofere feedback și aprobare pentru elementele grafice în termen de 5 zile lucrătoare;</li>
    <li>Se obligă să nu utilizeze serviciile în scopuri ilegale;</li>
    <li>Va respecta drepturile de proprietate intelectuală ale Prestatorului asupra elementelor grafice și funcționalităților create.</li>
</ul>

<h2 style="color: #1e40af; font-size: 16px; border-bottom: 2px solid #1e40af; padding-bottom: 5px; margin-top: 30px;">Art.5 Confidențialitate părților</h2>

<p>5.1. Părțile se obligă să păstreze confidențialitatea tuturor informațiilor comerciale, tehnice sau de orice altă natură obținute în cursul executării prezentului contract.</p>

<p>5.2. Această obligație de confidențialitate va rămâne în vigoare și după încetarea prezentului contract.</p>

<h2 style="color: #1e40af; font-size: 16px; border-bottom: 2px solid #1e40af; padding-bottom: 5px; margin-top: 30px;">Art. 6 Drepturi de autor</h2>

<p>6.1. Elementele create specific pentru Beneficiar (logo, grafice personalizate) rămân proprietatea acestuia după plata integrală.</p>

<p>6.2. Codul sursă și componentele tehnice rămân proprietatea Prestatorului, Beneficiarul primind doar drept de utilizare.</p>

<h2 style="color: #1e40af; font-size: 16px; border-bottom: 2px solid #1e40af; padding-bottom: 5px; margin-top: 30px;">Art. 7 Garanții/Limitări</h2>

<p>7.1. Prestatorul garantează funcționarea corectă a serviciilor conform specificațiilor agreate pentru o perioadă de 12 luni de la data predării.</p>

<p>7.2. Prestatorul nu este responsabil pentru: disfuncționalități cauzate de modificări efectuate de terți, probleme cauzate de furnizori terți (hosting, etc.), pierderi de date cauzate de factori externi.</p>

<p>7.3. Beneficiarul înțelege și acceptă că serviciile de consultanță și configurare nu garantează rezultate specifice în termeni de trafic, vânzări sau clasamente în motoarele de căutare.</p>

<h2 style="color: #1e40af; font-size: 16px; border-bottom: 2px solid #1e40af; padding-bottom: 5px; margin-top: 30px;">Art. 8 Protecția datelor cu caracter personal – GDPR</h2>

<p>8.1. Ambele părți se obligă să respecte prevederile Regulamentului (UE) 2016/679 privind protecția persoanelor fizice în ceea ce privește prelucrarea datelor cu caracter personal (GDPR).</p>

<p>8.2. Prestatorul va prelucra datele cu caracter personal doar în scopul și în măsura necesară executării prezentului contract și va implementa măsuri tehnice și organizatorice adecvate pentru protejarea acestora.</p>

<p>8.3. În cazul în care Prestatorul acționează ca persoană împuternicită de operator pentru prelucrarea datelor cu caracter personal în numele Beneficiarului, părțile vor încheia un acord separat de prelucrare a datelor.</p>

<h2 style="color: #1e40af; font-size: 16px; border-bottom: 2px solid #1e40af; padding-bottom: 5px; margin-top: 30px;">Art. 9 Rezilierea</h2>

<p>9.1. Oricare dintre părți poate rezilia contractul cu un preaviz de 30 de zile.</p>

<p>9.2. În cazul rezilierii de către Beneficiar, sumele deja achitate nu sunt rambursabile, acestea acoperind serviciile prestate până la data rezilierii.</p>

<p>9.3. În cazul rezilierii de către Prestator din motive imputabile Beneficiarului (neplată, încălcări repetate ale contractului), Beneficiarul va achita integral serviciile prestate până la data rezilierii.</p>

<h2 style="color: #1e40af; font-size: 16px; border-bottom: 2px solid #1e40af; padding-bottom: 5px; margin-top: 30px;">Art. 10 Forța Majoră</h2>

<p>10.1. Niciuna dintre părți nu va fi răspunzătoare pentru neexecutarea obligațiilor în caz de forță majoră.</p>

<p>10.2. Partea afectată va notifica cealaltă parte în termen de 5 zile de la apariția cazului de forță majoră.</p>

<h2 style="color: #1e40af; font-size: 16px; border-bottom: 2px solid #1e40af; padding-bottom: 5px; margin-top: 30px;">Art. 11 Modificarea și Completarea</h2>

<p>Orice modificare a prezentului contract se va face doar prin act adițional semnat de ambele părți.</p>

<h2 style="color: #1e40af; font-size: 16px; border-bottom: 2px solid #1e40af; padding-bottom: 5px; margin-top: 30px;">Art. 12 Jurisdicție și Legea Aplicabilă</h2>

<p>12.1. Prezentul contract este supus legii române. Orice litigiu va fi soluționat pe cale amiabilă, iar în caz contrar, de instanțele competente din România.</p>

<p>12.2. Pentru litigiile ce decurg din sau în legătură cu prezentul contract, părțile convin să se adreseze instanțelor competente de la sediul Prestatorului.</p>

<p style="margin-top: 30px;">Încheiat în 2 exemplare, câte unul pentru fiecare parte.</p>

{{SIGNATURES}}
HTML;

        ContractTemplate::updateOrCreate(
            ['id' => 1],
            [
                'organization_id' => 1,
                'name' => 'Contract Prestări Servicii Web',
                'category' => 'servicii',
                'is_default' => true,
                'is_active' => true,
                'content' => $content,
            ]
        );
    }
}
