export default function Footer() {
  return (
    <footer className="footer">
      <span className="footer-tag">JobBoard</span>
      <span>
        © {new Date().getFullYear()} JobBoard · Votre prochain emploi commence
        ici.
      </span>
    </footer>
  );
}
