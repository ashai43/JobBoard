import { BrowserRouter, Navigate, Route, Routes } from "react-router-dom";
import Admin from "./pages/Admin.jsx";
import Candidate from "./pages/Candidate.jsx";
import Company from "./pages/Company.jsx";
import Home from "./pages/Home.jsx";
import Login from "./pages/Login.jsx";
import PublishOffer from "./pages/PublishOffer.jsx";
import Signup from "./pages/Signup.jsx";
import Footer from "./pages/components/Footer.jsx";
import Header from "./pages/components/Header.jsx";

export default function App() {
  return (
    <BrowserRouter>
      <Header />
      <main className="app-main">
        {/* déclaration des routes de l'application */}
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/connexion" element={<Login />} />
          <Route path="/inscription" element={<Signup />} />
          <Route path="/candidat" element={<Candidate />} />
          <Route path="/entreprise" element={<Company />} />
          <Route path="/offre/nouvelle" element={<PublishOffer />} />
          <Route path="/administration" element={<Admin />} />
          <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
      </main>
      <Footer />
    </BrowserRouter>
  );
}
