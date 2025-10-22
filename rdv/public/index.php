<?php
require_once __DIR__ . '/../lib.php';
$pdo = db();
// Prefetch minimal lists for initial render
$specialists = $pdo->query('SELECT id,name,title,category FROM specialists WHERE active=1 ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$treatments = $pdo->query('SELECT id,name,duration_min FROM treatments WHERE active=1 ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
?><!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Réserver — Clinique Bellecour</title>
    <link rel="stylesheet" href="/assets/css/style.css" />
    <style>
      body { background:#0b0b0b; color:#f5f5f5 }
      .shell { max-width: 1000px; margin: 28px auto; padding: 0 16px; }
      .card { background: linear-gradient(135deg, #0f0f0f, #151515); border:1px solid #1f2937; border-radius: 14px; padding: 20px; }
      .grid { display:grid; gap:12px; grid-template-columns: 1fr 1fr; }
      select, input, textarea { background:#0f1115; color:#e5e7eb; border:1px solid #2a3442; border-radius:10px; padding:10px; width:100% }
      .btn { padding:10px 14px; border-radius:10px; border:1px solid #2a3442; background:linear-gradient(135deg,#d4af37,#c19b2e); color:#111; font-weight:800; text-decoration:none }
      .slots { display:flex; flex-wrap:wrap; gap:8px }
      .slot { padding:10px 12px; border-radius:10px; border:1px solid #2a3442; background:#0f1115; cursor:pointer }
      .slot[disabled] { opacity:.4; cursor:not-allowed }
      @media (max-width: 760px){ .grid {grid-template-columns:1fr} }
    </style>
  </head>
  <body>
    <div class="shell">
      <div class="card">
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px">
          <img src="/assets/img/logo_cliniquebellecour.png" width="28" height="28" style="border-radius:8px" alt="" />
          <h1 style="margin:0">Réserver un rendez-vous</h1>
        </div>
        <form id="booking" onsubmit="return false">
          <div class="grid">
            <label>Spécialiste
              <select id="specialist" required>
                <option value="">Sélectionner</option>
                <?php foreach ($specialists as $s): ?>
                  <option value="<?= (int)$s['id'] ?>"><?php echo h($s['name']) . ' — ' . h($s['category']); ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <label>Prestation
              <select id="treatment" required>
                <option value="">Sélectionner</option>
                <?php foreach ($treatments as $t): ?>
                  <option value="<?= (int)$t['id'] ?>"><?php echo h($t['name']) . ' (' . (int)$t['duration_min'] . ' min)'; ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <label>Date souhaitée
              <input type="date" id="date" min="<?= date('Y-m-d') ?>" required />
            </label>
            <div>
              <div class="muted" style="margin:8px 0">Créneaux disponibles</div>
              <div id="slots" class="slots"></div>
            </div>
          </div>
          <hr style="border:none; border-top:1px solid #1f2937; margin:18px 0">
          <div class="grid">
            <label>Nom et prénom<input id="name" required placeholder="Votre nom complet" /></label>
            <label>Téléphone<input id="phone" placeholder="+41 ..." /></label>
            <label style="grid-column:1 / -1">E-mail<input id="email" type="email" placeholder="nom@domaine.ch" /></label>
            <label style="grid-column:1 / -1">Message<textarea id="notes" rows="3" placeholder="Votre demande"></textarea></label>
          </div>
          <p class="muted" style="margin:10px 0">Vous recevrez une confirmation sous réserve de disponibilité. Données traitées en toute confidentialité.</p>
          <div style="display:flex; gap:10px; flex-wrap:wrap">
            <button class="btn" id="submit">Demander le rendez-vous</button>
            <a class="btn" href="/" style="background:none;color:#e5e7eb">← Retour au site</a>
          </div>
        </form>
        <div id="msg" style="margin-top:12px"></div>
      </div>
    </div>
    <script>
      const el = (sel) => document.querySelector(sel);
      const slotsWrap = el('#slots');
      let selectedStart = null;
      function renderSlots(list){
        slotsWrap.innerHTML = '';
        if (!list || !list.length) { slotsWrap.textContent = 'Aucun créneau disponible pour cette date.'; return; }
        list.forEach(s => {
          const b = document.createElement('button');
          b.type = 'button'; b.className = 'slot'; b.textContent = s.start + ' – ' + s.end;
          b.addEventListener('click', () => { selectedStart = s.start; document.querySelectorAll('.slot').forEach(x=>x.style.borderColor='#2a3442'); b.style.borderColor = '#d4af37'; });
          slotsWrap.appendChild(b);
        });
      }
      async function fetchSlots(){
        selectedStart = null; slotsWrap.textContent = 'Chargement…';
        const specialist = el('#specialist').value, treatment = el('#treatment').value, date = el('#date').value;
        if (!specialist || !treatment || !date) { slotsWrap.textContent = '—'; return; }
        const r = await fetch(`/rdv/api.php?action=slots&specialist_id=${specialist}&treatment_id=${treatment}&date=${date}`);
        renderSlots(await r.json());
      }
      document.querySelectorAll('#specialist,#treatment,#date').forEach(i=> i.addEventListener('change', fetchSlots));
      el('#submit').addEventListener('click', async ()=>{
        const specialist = el('#specialist').value, treatment = el('#treatment').value, date = el('#date').value;
        const name = el('#name').value, email = el('#email').value, phone = el('#phone').value, notes = el('#notes').value;
        if (!specialist || !treatment || !date || !name || !selectedStart) { alert('Veuillez compléter le formulaire et choisir un créneau.'); return; }
        const fd = new FormData();
        fd.set('action','book'); fd.set('specialist_id',specialist); fd.set('treatment_id',treatment); fd.set('date',date); fd.set('start',selectedStart);
        fd.set('name',name); fd.set('email',email); fd.set('phone',phone); fd.set('notes',notes);
        const r = await fetch('/rdv/api.php', { method:'POST', body: fd });
        const j = await r.json();
        if (j.ok) { el('#msg').textContent = 'Votre demande a été enregistrée. Nous revenons vers vous rapidement.'; document.getElementById('booking').reset(); slotsWrap.textContent='—'; } else { el('#msg').textContent = 'Erreur: ' + (j.error||'indéterminée'); }
      });
    </script>
  </body>
  </html>

