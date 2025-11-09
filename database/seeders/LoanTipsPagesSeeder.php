<?php

namespace Database\Seeders;

use App\Domains\PageBuilder\Models\Page;
use Illuminate\Database\Seeder;

class LoanTipsPagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user as author (or create a default one)
        $authorId = \App\Domains\Security\UserManagement\Models\User::first()?->id ?? 1;

        $pages = [
            // Landing Page
            [
                'title' => 'Home - Smart Loan Tips & Financial Guidance',
                'slug' => 'home',
                'status' => 'published',
                'author_id' => $authorId,
                'meta_title' => 'Smart Loan Tips | Expert Financial Guidance for Better Borrowing',
                'meta_description' => 'Get expert loan tips and financial guidance. Learn how to secure better rates, improve your credit score, and make smarter borrowing decisions.',
                'meta_keywords' => 'loan tips, personal loans, mortgage advice, credit score, financial guidance',
                'data' => [
                    'layout' => 'default',
                    'sections' => [
                        [
                            'id' => 'hero-1',
                            'type' => 'hero',
                            'config' => [
                                'title' => 'Make Smarter Borrowing Decisions',
                                'subtitle' => 'EXPERT FINANCIAL GUIDANCE',
                                'description' => 'Navigate the world of loans with confidence. Get expert tips, compare rates, and learn strategies to save thousands on your next loan.',
                                'primaryButtonText' => 'Explore Loan Tips',
                                'primaryButtonUrl' => '/blog',
                                'secondaryButtonText' => 'Check Your Rate',
                                'secondaryButtonUrl' => '/contact',
                                'textAlign' => 'center',
                            ],
                        ],
                        [
                            'id' => 'stats-1',
                            'type' => 'stats',
                            'config' => [
                                'title' => 'Helping Thousands Save on Loans',
                                'stats' => [
                                    ['value' => '$2.5M+', 'label' => 'Saved', 'description' => 'By our readers'],
                                    ['value' => '50K+', 'label' => 'Readers', 'description' => 'Monthly visitors'],
                                    ['value' => '4.9/5', 'label' => 'Rating', 'description' => 'User satisfaction'],
                                    ['value' => '1000+', 'label' => 'Articles', 'description' => 'Expert guides'],
                                ],
                            ],
                        ],
                        [
                            'id' => 'features-1',
                            'type' => 'features',
                            'config' => [
                                'title' => 'Why Choose Our Loan Guidance',
                                'subtitle' => 'Everything you need to make informed borrowing decisions',
                                'columns' => 3,
                                'features' => [
                                    [
                                        'icon' => 'ph:shield-check',
                                        'title' => 'Expert Advice',
                                        'description' => 'Get tips from financial experts with decades of experience in lending and personal finance.',
                                    ],
                                    [
                                        'icon' => 'ph:chart-line',
                                        'title' => 'Save Money',
                                        'description' => 'Learn strategies to secure lower interest rates and save thousands over the life of your loan.',
                                    ],
                                    [
                                        'icon' => 'ph:graduation-cap',
                                        'title' => 'Educational Resources',
                                        'description' => 'Access comprehensive guides, calculators, and tools to understand every aspect of borrowing.',
                                    ],
                                    [
                                        'icon' => 'ph:users-three',
                                        'title' => 'Community Support',
                                        'description' => 'Join thousands of readers sharing experiences and learning together.',
                                    ],
                                    [
                                        'icon' => 'ph:clock',
                                        'title' => 'Up-to-Date Information',
                                        'description' => 'Stay current with the latest lending trends, rates, and regulations.',
                                    ],
                                    [
                                        'icon' => 'ph:check-circle',
                                        'title' => 'Unbiased Reviews',
                                        'description' => 'Honest, transparent reviews of lenders and loan products with no hidden agendas.',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => 'cta-1',
                            'type' => 'cta',
                            'config' => [
                                'title' => 'Ready to Save on Your Next Loan?',
                                'description' => 'Subscribe to our newsletter for weekly tips and exclusive guides',
                                'buttonText' => 'Get Free Tips',
                                'buttonUrl' => '/contact',
                                'pattern' => 'gradient',
                            ],
                        ],
                    ],
                ],
            ],

            // About Us Page
            [
                'title' => 'About Us - Your Trusted Loan Advisors',
                'slug' => 'about',
                'status' => 'published',
                'author_id' => $authorId,
                'meta_title' => 'About Us | Your Trusted Source for Loan Tips',
                'meta_description' => 'Learn about our mission to help people make smarter borrowing decisions and save money on loans.',
                'meta_keywords' => 'about us, loan advisors, financial experts, mission',
                'data' => [
                    'layout' => 'default',
                    'sections' => [
                        [
                            'id' => 'hero-about',
                            'type' => 'hero',
                            'config' => [
                                'title' => 'Empowering Smart Borrowing Decisions',
                                'subtitle' => 'ABOUT US',
                                'description' => 'We believe everyone deserves access to expert financial guidance and the knowledge to make informed borrowing decisions.',
                                'primaryButtonText' => 'Read Our Story',
                                'primaryButtonUrl' => '#story',
                                'secondaryButtonText' => 'Meet the Team',
                                'secondaryButtonUrl' => '#team',
                                'textAlign' => 'center',
                            ],
                        ],
                        [
                            'id' => 'features-mission',
                            'type' => 'features',
                            'config' => [
                                'title' => 'Our Mission & Values',
                                'subtitle' => 'What drives us every day',
                                'columns' => 3,
                                'features' => [
                                    [
                                        'icon' => 'ph:target',
                                        'title' => 'Transparency First',
                                        'description' => 'We provide honest, unbiased information without hidden fees or conflicts of interest.',
                                    ],
                                    [
                                        'icon' => 'ph:lightbulb',
                                        'title' => 'Education Focused',
                                        'description' => 'Empowering readers with knowledge to make confident financial decisions.',
                                    ],
                                    [
                                        'icon' => 'ph:heart',
                                        'title' => 'Community Driven',
                                        'description' => 'Building a supportive community where people help each other succeed financially.',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => 'team-1',
                            'type' => 'team',
                            'config' => [
                                'title' => 'Meet Our Expert Team',
                                'subtitle' => 'Financial professionals dedicated to your success',
                                'columns' => 3,
                                'members' => [
                                    [
                                        'name' => 'Sarah Johnson',
                                        'role' => 'Founder & Chief Editor',
                                        'bio' => '15+ years in mortgage lending and financial advisory.',
                                    ],
                                    [
                                        'name' => 'Michael Chen',
                                        'role' => 'Senior Financial Analyst',
                                        'bio' => 'Former bank executive specializing in personal loans.',
                                    ],
                                    [
                                        'name' => 'Emily Rodriguez',
                                        'role' => 'Credit Expert',
                                        'bio' => 'Certified credit counselor with 10 years experience.',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => 'cta-about',
                            'type' => 'cta',
                            'config' => [
                                'title' => 'Have Questions?',
                                'description' => 'Our team is here to help you make the best borrowing decisions',
                                'buttonText' => 'Contact Us',
                                'buttonUrl' => '/contact',
                            ],
                        ],
                    ],
                ],
            ],

            // Contact Page
            [
                'title' => 'Contact Us - Get in Touch',
                'slug' => 'contact',
                'status' => 'published',
                'author_id' => $authorId,
                'meta_title' => 'Contact Us | Get Expert Loan Advice',
                'meta_description' => 'Have questions about loans? Contact our expert team for personalized guidance.',
                'meta_keywords' => 'contact, loan questions, financial advice, get in touch',
                'data' => [
                    'layout' => 'default',
                    'sections' => [
                        [
                            'id' => 'hero-contact',
                            'type' => 'hero',
                            'config' => [
                                'title' => 'Let\'s Talk About Your Financial Goals',
                                'subtitle' => 'GET IN TOUCH',
                                'description' => 'Have questions about loans, rates, or need personalized advice? We\'re here to help.',
                                'textAlign' => 'center',
                            ],
                        ],
                        [
                            'id' => 'contact-form-1',
                            'type' => 'contact-form',
                            'config' => [
                                'title' => 'Send Us a Message',
                                'description' => 'Fill out the form below and we\'ll get back to you within 24 hours.',
                                'submitUrl' => '/api/contact',
                                'successMessage' => 'Thank you for reaching out! We\'ll respond within 24 hours.',
                                'showPhone' => true,
                                'showSubject' => true,
                            ],
                        ],
                        [
                            'id' => 'faq-contact',
                            'type' => 'faq',
                            'config' => [
                                'title' => 'Quick Answers',
                                'subtitle' => 'Frequently asked questions',
                                'items' => [
                                    [
                                        'question' => 'How quickly will I receive a response?',
                                        'answer' => 'We aim to respond to all inquiries within 24 hours during business days.',
                                    ],
                                    [
                                        'question' => 'Do you provide personalized loan advice?',
                                        'answer' => 'Yes! While we provide general educational content, we can offer guidance tailored to your specific situation.',
                                    ],
                                    [
                                        'question' => 'Is your service free?',
                                        'answer' => 'All our educational content and basic guidance is completely free. We may earn commissions from lenders we recommend, but this never affects our honest advice.',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // Privacy Policy
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy',
                'status' => 'published',
                'author_id' => $authorId,
                'meta_title' => 'Privacy Policy | Smart Loan Tips',
                'meta_description' => 'Our commitment to protecting your privacy and personal information.',
                'data' => [
                    'layout' => 'default',
                    'sections' => [
                        [
                            'id' => 'legal-privacy',
                            'type' => 'legal-text',
                            'config' => [
                                'title' => 'Privacy Policy',
                                'lastUpdated' => now()->format('F d, Y'),
                                'content' => 'At Smart Loan Tips, we are committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website.',
                                'sections' => [
                                    [
                                        'heading' => '1. Information We Collect',
                                        'content' => 'We collect information that you provide directly to us, including:

- Name and contact information when you subscribe to our newsletter
- Email address and message content when you contact us
- Usage data and analytics through cookies and similar technologies
- Device information and IP addresses for security purposes',
                                    ],
                                    [
                                        'heading' => '2. How We Use Your Information',
                                        'content' => 'We use the information we collect to:

- Provide, maintain, and improve our services
- Send you newsletters and educational content you requested
- Respond to your inquiries and provide customer support
- Analyze usage patterns to improve our website
- Protect against fraudulent or illegal activity',
                                    ],
                                    [
                                        'heading' => '3. Information Sharing',
                                        'content' => 'We do not sell your personal information. We may share your information with:

- Service providers who assist in operating our website
- Analytics providers to help us understand site usage
- Law enforcement when required by law
- Business partners with your explicit consent',
                                    ],
                                    [
                                        'heading' => '4. Cookies and Tracking',
                                        'content' => 'We use cookies and similar technologies to:

- Remember your preferences and settings
- Analyze site traffic and usage patterns
- Provide personalized content
- Enable social media features

You can control cookies through your browser settings.',
                                    ],
                                    [
                                        'heading' => '5. Your Rights',
                                        'content' => 'You have the right to:

- Access the personal information we hold about you
- Request correction of inaccurate information
- Request deletion of your information
- Opt-out of marketing communications
- Object to processing of your information

Contact us to exercise these rights.',
                                    ],
                                    [
                                        'heading' => '6. Data Security',
                                        'content' => 'We implement appropriate security measures to protect your information. However, no method of transmission over the internet is 100% secure, and we cannot guarantee absolute security.',
                                    ],
                                    [
                                        'heading' => '7. Changes to This Policy',
                                        'content' => 'We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new policy on this page and updating the "Last Updated" date.',
                                    ],
                                    [
                                        'heading' => '8. Contact Us',
                                        'content' => 'If you have questions about this Privacy Policy, please contact us through our contact form or email us directly.',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // Terms of Service
            [
                'title' => 'Terms of Service',
                'slug' => 'terms',
                'status' => 'published',
                'author_id' => $authorId,
                'meta_title' => 'Terms of Service | Smart Loan Tips',
                'meta_description' => 'Terms and conditions for using our loan tips and financial guidance services.',
                'data' => [
                    'layout' => 'default',
                    'sections' => [
                        [
                            'id' => 'legal-terms',
                            'type' => 'legal-text',
                            'config' => [
                                'title' => 'Terms of Service',
                                'lastUpdated' => now()->format('F d, Y'),
                                'content' => 'Welcome to Smart Loan Tips. By accessing and using our website, you agree to be bound by these Terms of Service.',
                                'sections' => [
                                    [
                                        'heading' => '1. Acceptance of Terms',
                                        'content' => 'By accessing this website, you accept these terms and conditions in full. If you disagree with any part of these terms, you must not use our website.',
                                    ],
                                    [
                                        'heading' => '2. Educational Purpose',
                                        'content' => 'The information on this website is provided for educational and informational purposes only. It does not constitute financial advice, and should not be relied upon as such. Always consult with qualified financial professionals before making borrowing decisions.',
                                    ],
                                    [
                                        'heading' => '3. No Warranties',
                                        'content' => 'We provide our content "as is" without any warranties, express or implied. We do not guarantee the accuracy, completeness, or usefulness of any information on this site. Interest rates, loan terms, and lender offerings change frequently.',
                                    ],
                                    [
                                        'heading' => '4. Affiliate Relationships',
                                        'content' => 'We may earn commissions from lenders and financial institutions we recommend. However, our editorial integrity is never compromised by these relationships. We only recommend products and services we genuinely believe can benefit our readers.',
                                    ],
                                    [
                                        'heading' => '5. User Conduct',
                                        'content' => 'You agree not to:

- Use the site for any unlawful purpose
- Attempt to gain unauthorized access to our systems
- Transmit viruses or malicious code
- Harass other users or our staff
- Scrape or copy content without permission',
                                    ],
                                    [
                                        'heading' => '6. Intellectual Property',
                                        'content' => 'All content on this website, including text, graphics, logos, and images, is our property or licensed to us. You may not reproduce, distribute, or create derivative works without our written permission.',
                                    ],
                                    [
                                        'heading' => '7. Limitation of Liability',
                                        'content' => 'We shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use of or inability to use the site, including but not limited to financial losses from borrowing decisions.',
                                    ],
                                    [
                                        'heading' => '8. Third-Party Links',
                                        'content' => 'Our site may contain links to third-party websites. We are not responsible for the content, privacy policies, or practices of these external sites.',
                                    ],
                                    [
                                        'heading' => '9. Modifications',
                                        'content' => 'We reserve the right to modify these terms at any time. Changes will be effective immediately upon posting to the website. Your continued use constitutes acceptance of modified terms.',
                                    ],
                                    [
                                        'heading' => '10. Governing Law',
                                        'content' => 'These terms shall be governed by and construed in accordance with applicable laws. Any disputes shall be subject to the exclusive jurisdiction of the appropriate courts.',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // Cookie Policy
            [
                'title' => 'Cookie Policy',
                'slug' => 'cookies',
                'status' => 'published',
                'author_id' => $authorId,
                'meta_title' => 'Cookie Policy | Smart Loan Tips',
                'meta_description' => 'Learn about how we use cookies and similar technologies on our website.',
                'data' => [
                    'layout' => 'default',
                    'sections' => [
                        [
                            'id' => 'legal-cookies',
                            'type' => 'legal-text',
                            'config' => [
                                'title' => 'Cookie Policy',
                                'lastUpdated' => now()->format('F d, Y'),
                                'content' => 'This Cookie Policy explains how Smart Loan Tips uses cookies and similar technologies to recognize you when you visit our website.',
                                'sections' => [
                                    [
                                        'heading' => '1. What Are Cookies?',
                                        'content' => 'Cookies are small data files that are placed on your computer or mobile device when you visit a website. Cookies are widely used by website owners to make their websites work more efficiently and to provide reporting information.',
                                    ],
                                    [
                                        'heading' => '2. Why We Use Cookies',
                                        'content' => 'We use cookies for several reasons:

- Essential cookies: Required for the website to function properly
- Performance cookies: Help us understand how visitors interact with our site
- Functionality cookies: Remember your preferences and settings
- Marketing cookies: Track your activity to deliver relevant advertisements',
                                    ],
                                    [
                                        'heading' => '3. Types of Cookies We Use',
                                        'content' => 'Session Cookies: Temporary cookies that expire when you close your browser

Persistent Cookies: Remain on your device for a set period or until you delete them

First-Party Cookies: Set by our website directly

Third-Party Cookies: Set by external services we use, such as Google Analytics',
                                    ],
                                    [
                                        'heading' => '4. Specific Cookies We Use',
                                        'content' => 'Analytics Cookies: Google Analytics to understand site usage and improve our content

Preference Cookies: Remember your site preferences like dark mode

Security Cookies: Protect against fraudulent activity and enhance security

Marketing Cookies: Deliver relevant advertisements and measure campaign effectiveness',
                                    ],
                                    [
                                        'heading' => '5. Managing Cookies',
                                        'content' => 'You can control and manage cookies in several ways:

Browser Settings: Most browsers allow you to refuse cookies or delete existing ones

Opt-Out Tools: Use tools provided by advertising networks to opt out of targeted ads

Cookie Consent Tool: Use our cookie consent tool to customize your preferences

Note that disabling cookies may affect website functionality.',
                                    ],
                                    [
                                        'heading' => '6. Third-Party Cookies',
                                        'content' => 'We use services from third parties who may set cookies:

Google Analytics: For website analytics and reporting
Social Media Platforms: For sharing functionality
Advertising Networks: For relevant advertisements

These third parties have their own privacy policies.',
                                    ],
                                    [
                                        'heading' => '7. Updates to This Policy',
                                        'content' => 'We may update this Cookie Policy to reflect changes in technology, legislation, or our practices. Please check this page periodically for updates.',
                                    ],
                                    [
                                        'heading' => '8. Contact Us',
                                        'content' => 'If you have questions about our use of cookies or this Cookie Policy, please contact us through our contact form.',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($pages as $pageData) {
            Page::updateOrCreate(
                ['slug' => $pageData['slug']],
                $pageData
            );
        }

        $this->command->info('Loan tips pages created successfully!');
    }
}
