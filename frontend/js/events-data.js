/**
 * COGNOS 2K26 - Events Dataset
 * Centralized data store for event cards, modal popups, and registration binding.
 * Clean, standard encoding with zero broken characters.
 */

const COGNOS_EVENTS_DATA = {
    vishleshana: {
        id: "vishleshana",
        name: "Vishleshana",
        tagNumber: "#01",
        category: "Group Discussion",
        timingDisplay: "2:00 PM - 5:00 PM",
        date: "October 9, 2026 (Friday)",
        teamSize: "Individual",
        prizePool: "Rs. 6,000",
        prizesBreakdown: [
            { pos: "1st Place", amt: "Rs. 3,000" },
            { pos: "2nd Place", amt: "Rs. 2,000" },
            { pos: "3rd Place", amt: "Rs. 1,000" }
        ],
        venue: "Panel 1: CM SCE Lab / Panel 2: Dassault Systemes Lab, Decennial Block - III Floor",
        about: "A group discussion event evaluating analytical thinking, spontaneous communication skills, teamwork, and structured awareness on contemporary technical and socio-economic issues.",
        rules: [
            "Original College ID card is mandatory for entry.",
            "Individual participation only.",
            "Discussion topics are given on the spot.",
            "Groups of 5 to 8 participants per panel.",
            "Preparation time: 5 minutes.",
            "Discussion duration: 10 to 15 minutes.",
            "English language only throughout the rounds.",
            "The decision of the judging panel is final and binding."
        ],
        coordinators: {
            faculty: [
                { name: "Dr. Ganji Ramanjaiah", phone: "9848332853" },
                { name: "Dr. Riaz Shaik", phone: "9966743943" }
            ],
            students: [
                { name: "Bonamukkala Ajay", phone: "7207039202" },
                { name: "Shaik Ayesha Rizwana", phone: "7207039202" }
            ]
        }
    },

    razzle_review: {
        id: "razzle_review",
        name: "Razzle Review",
        tagNumber: "#02",
        category: "Paper Presentation",
        timingDisplay: "From 11:00 AM onwards",
        date: "October 9, 2026 (Friday)",
        teamSize: "Max 2 Members",
        prizePool: "Rs. 6,000",
        prizesBreakdown: [
            { pos: "1st Place", amt: "Rs. 3,000" },
            { pos: "2nd Place", amt: "Rs. 2,000" },
            { pos: "3rd Place", amt: "Rs. 1,000" }
        ],
        venue: "CM SCE Lab, Decennial Block - III Floor",
        about: "A research paper presentation symposium encouraging students to present innovative engineering ideas and emerging technological advancements with clarity, technical depth, and rigorous defense.",
        domains: [
            "Big Data and Cloud Computing",
            "Natural Language Processing (NLP)",
            "Cybersecurity and Data Privacy",
            "Internet of Things (IoT)",
            "Bioinformatics and Healthcare Analytics",
            "Machine Learning and Artificial Intelligence",
            "Data Mining and Analytics",
            "Quantum, Nano, Distributed, Mobile & Parallel Computing",
            "Machine Intelligence, HCI, Robotics & Emerging Trends"
        ],
        rules: [
            "Original College ID card is mandatory for all team members.",
            "Paper submission must follow standard IEEE format.",
            "Presentation time: 8 minutes + 2 minutes Q&A with evaluation jury.",
            "Original research and novel implementations are preferred.",
            "Soft copy of the paper must be emailed to cognos.csd@gmail.com with subject [Razzle Review - RegID].",
            "Maximum 2 members per presenting team.",
            "Working prototypes or code implementations carry additional weightage."
        ],
        coordinators: {
            faculty: [
                { name: "Dr. Ch. Sudha Sree", phone: "9494641234" },
                { name: "Dr. Vallabhajosyula Sasikala", phone: "7976835016" }
            ],
            students: [
                { name: "Ponnaluri Jeyanth", phone: "8331927193" },
                { name: "N Gayatri", phone: "8331927193" }
            ]
        }
    },

    data_dazzle: {
        id: "data_dazzle",
        name: "Data Dazzle",
        tagNumber: "#03",
        category: "Data Storytelling & Dashboard",
        timingDisplay: "1:00 PM - 3:00 PM",
        date: "October 9, 2026 (Friday)",
        teamSize: "2 Members",
        prizePool: "Rs. 6,000",
        prizesBreakdown: [
            { pos: "1st Place", amt: "Rs. 3,000" },
            { pos: "2nd Place", amt: "Rs. 2,000" },
            { pos: "3rd Place", amt: "Rs. 1,000" }
        ],
        venue: "Dassault Systemes Lab, Decennial Block - III Floor",
        about: "A data visual analytics challenge focused on transforming multi-dimensional datasets into interactive visual dashboards and communicating actionable insights effectively.",
        rules: [
            "All participants must carry a valid physical College ID card.",
            "Registration must be completed prior to the symposium.",
            "Each team consists of exactly two participants.",
            "Each participant will receive an individual certificate of participation.",
            "Each team must design an interactive dashboard using any BI tool (Power BI, Tableau, Python, Streamlit, etc.).",
            "Presentation duration: 10 to 15 minutes per team.",
            "Plagiarized or duplicated dashboard designs will lead to immediate disqualification.",
            "Participants must bring their own laptops with necessary software and datasets pre-installed."
        ],
        coordinators: {
            faculty: [
                { name: "Dr. R. V. Kishore Kumar", phone: "9885993494" },
                { name: "Mr. Rallabandi Ch S N P Sairam", phone: "8328505878" }
            ],
            students: [
                { name: "Jarabana Krishna Kanth", phone: "7013162268" },
                { name: "Sahitya.B", phone: "7013162268" }
            ]
        }
    }
};
