import { Link, useLocation } from "react-router-dom";
export default function Header() {
  const { pathname } = useLocation();
  const logged = [
    "/candidat",
    "/entreprise",
    "/offre/nouvelle",
    "/administration",
  ].includes(pathname);
  return (
    <header className="topbar">
      <Link className="brand" to="/">
        Job<span>Board</span>
      </Link>
      <nav>
        <Link className={pathname === "/" ? "active" : ""} to="/">
          Offres
        </Link>
        {pathname === "/candidat" && (
          <Link className="active" to="/candidat">
            Mes candidatures
          </Link>
        )}
        {pathname === "/entreprise" || pathname === "/offre/nouvelle" ? (
          <Link className="active" to="/entreprise">
            Espace entreprise
          </Link>
        ) : null}
        {pathname === "/administration" && (
          <Link className="active" to="/administration">
            Administration
          </Link>
        )}
        {!logged ? (
          <>
            <Link
              className={pathname === "/connexion" ? "active" : ""}
              to="/connexion"
            >
              Connexion
            </Link>
            <Link className="nav-cta" to="/inscription">
              Inscription
            </Link>
          </>
        ) : (
          <>
            <span className="nav-user">
              {pathname === "/entreprise" || pathname === "/offre/nouvelle"
                ? "Nexa Systems"
                : pathname === "/administration"
                  ? "Admin"
                  : "Léa Martin"}
            </span>
            <Link className="nav-outline" to="/">
              Déconnexion
            </Link>
          </>
        )}
      </nav>
    </header>
  );
}
