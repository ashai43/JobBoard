import { useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import FormField from "./components/FormField.jsx";
export default function Signup() {
  const [form, setForm] = useState({
      first: "Léa",
      last: "Martin",
      email: "lea.martin@mail.com",
      password: "motdepasse",
      confirm: "",
    }),
    [error, setError] = useState("");
  const navigate = useNavigate();
  const change = (k) => (e) => setForm({ ...form, [k]: e.target.value });
  return (
    <section className="auth-wrap">
      <form
        className="panel signup-panel"
        onSubmit={(e) => {
          e.preventDefault();
          if (form.password !== form.confirm) {
            setError("Les mots de passe ne correspondent pas.");
            return;
          }
          setError("");
          navigate("/candidat");
        }}
      >
        <h1>Créer un compte candidat</h1>
        <p className="muted">Quelques informations pour postuler en un clic</p>
        <div className="form-grid two">
          <FormField
            label="Prénom"
            value={form.first}
            onChange={change("first")}
          />
          <FormField label="Nom" value={form.last} onChange={change("last")} />
        </div>
        <FormField
          label="Email"
          type="email"
          value={form.email}
          onChange={change("email")}
        />
        <FormField
          label="Mot de passe"
          type="password"
          value={form.password}
          onChange={change("password")}
        />
        <FormField
          label="Confirmer le mot de passe"
          type="password"
          value={form.confirm}
          onChange={change("confirm")}
        />
        {error && <p className="error">{error}</p>}
        <button className="btn btn-primary btn-block">Créer mon compte</button>
        <p className="auth-foot">
          Déjà inscrit ? <Link to="/connexion">Connexion</Link>
        </p>
      </form>
    </section>
  );
}
