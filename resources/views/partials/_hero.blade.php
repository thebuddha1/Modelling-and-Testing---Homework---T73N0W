<div class="bg-gradient-to-r from-blue-50 to-blue-100 p-8 rounded-lg shadow-lg mb-10 text-center">
  <h1 class="text-3xl sm:text-5xl font-extrabold text-blue-900 mb-4">
    Oszd meg motoros élményeidet a barátaiddal
  </h1>
  <p class="text-lg sm:text-xl text-gray-700 mb-6">
    Regisztrálj, hogy túrákat tervezhess, megoszthasd kedvenc helyeid a barátaiddal, 
    és csatlakozz egy megbízható közösséghez, ahol a fórumon segítőkész tagokkal beszélgethetsz, útvonalakat ajánlhatsz, és inspirációt meríthetsz más motorosok történeteiből.
  </p>

  <div class="flex flex-col sm:flex-row justify-center space-y-3 sm:space-y-0 sm:space-x-4 mb-6">
    <a href="/register" 
       class="text-white bg-blue-600 hover:bg-blue-700 px-4 sm:px-6 py-2 sm:py-3 rounded-md shadow-md text-base sm:text-lg font-semibold transition duration-300">
      Regisztráció
    </a>
    <button id="toggleBenefits" 
       class="text-blue-600 bg-white border border-blue-600 hover:bg-blue-50 px-4 sm:px-6 py-2 sm:py-3 rounded-md shadow-md text-base sm:text-lg font-semibold transition duration-300">
      Miért éri meg csatlakozni?
    </button>
  </div>

  <div id="benefits" class="max-w-2xl mx-auto overflow-hidden transition-all duration-700 ease-in-out max-h-0 opacity-0">
    <h2 class="text-2xl sm:text-3xl font-bold text-blue-900 mb-4 text-center">
      Mi vár rád?
    </h2>
    <p class="text-gray-700 text-lg sm:text-xl text-center mb-6">
      Az oldalunkon mindent megtalálsz, ami egy motoros számára fontos:
    </p>
    <ul class="text-gray-700 text-base sm:text-lg list-disc list-inside space-y-2 text-left">
      <li><strong>Túratervezés:</strong> Tervezd meg útvonalaid és fedezz fel új helyeket.</li>
      <li><strong>Kedvenc helyek megosztása:</strong> Mutasd meg barátaidnak a legjobb pihenőhelyeket, panorámákat és kanyarokat.</li>
      <li><strong>Fórum:</strong> Beszélgess más motorosokkal, kérdezz, vagy oszd meg a tapasztalataidat.</li>
      <li><strong>És még sok minden:</strong> Ez egy kezdeti főoldal dizájn vendég felhasználóknak, valószínűleg módosulni fog, a logó át lesz tervezve.</li>
    </ul>
  </div>
</div>

<script>
  const btn = document.getElementById('toggleBenefits');
  const benefits = document.getElementById('benefits');

  btn.addEventListener('click', () => {
    if (benefits.classList.contains('max-h-0')) {
      benefits.classList.remove('max-h-0', 'opacity-0');
      benefits.classList.add('max-h-[1000px]', 'opacity-100');
    } else {
      benefits.classList.remove('max-h-[1000px]', 'opacity-100');
      benefits.classList.add('max-h-0', 'opacity-0');
    }
  });
</script>
