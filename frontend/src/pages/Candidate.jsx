import { useState } from "react";
const initial = [
  [
    "Développeur Fullstack React / Symfony",
    "Nexa Systems",
    "12/10/2026",
    "Nouvelle",
  ],
  ["Alternance Data Engineer", "Orbis Data", "08/10/2026", "En cours"],
  ["Administrateur systèmes et réseaux", "Lumio", "30/09/2026", "Acceptée"],
  ["Chef de projet digital", "Atelier Pixel", "22/09/2026", "Refusée"],
];
export default function Candidate() {
  const [apps, setApps] = useState(initial);
  return (
    <section className="container page-content">
      <div className="profile panel">
        <div className="avatar">LM</div>
        <div>
          <h2>Léa Martin</h2>
          <p className="muted">lea.martin@mail.com · 06 12 34 56 78</p>
        </div>
      </div>
      <h1 className="page-title">Mes candidatures</h1>
      <div className="application-list">
        {apps.map((a, i) => (
          <article className="application panel" key={a[0]}>
            <div>
              <h3>{a[0]}</h3>
              <p className="muted">
                {a[1]} · Envoyée le {a[2]}
              </p>
            </div>
            <div className="application-actions">
              <span
                className={`status ${a[3].toLowerCase().replace(" ", "-")}`}
              >
                {a[3]}
              </span>
              {a[3] === "Nouvelle" && (
                <button
                  className="btn btn-danger btn-small"
                  onClick={() => setApps(apps.filter((_, idx) => idx !== i))}
                >
                  Retirer
                </button>
              )}
            </div>
          </article>
        ))}
      </div>
      <div className="pagination">
        <button className="btn btn-light btn-small">← Précédent</button>
        <span>Page 1 / 2</span>
        <button className="btn btn-light btn-small">Suivant →</button>
      </div>
    </section>
  );
}
